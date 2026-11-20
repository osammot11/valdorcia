<?php

namespace Tests\Feature;

use App\Jobs\SendTicketEmail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => self::SECRET]);
        Queue::fake();
    }

    private function pendingOrder(int $tickets = 2): Order
    {
        $order = Order::create([
            'group_code' => 'WFU_TEST' . rand(1000, 9999),
            'total_amount' => 28 * $tickets,
            'payment_method' => 'stripe',
            'status' => 'pending',
            'stripe_payment_intent_id' => 'pi_test_123',
        ]);

        for ($i = 0; $i < $tickets; $i++) {
            $order->tickets()->create([
                'price_paid' => 28, 'first_name' => 'Test' . $i, 'last_name' => 'Rossi', 'route_choice' => 'saporito',
                'dob' => '1985-04-12', 'birth_place' => 'Siena', 'residence_address' => 'Via di Prova 1', 'city' => 'Siena',
                'zip_code' => '53100', 'province' => 'Siena', 'region' => 'Toscana', 'country' => 'Italia', 'nationality' => 'IT',
                'email' => "test$i@example.com", 'phone' => '3330000000', 'status' => 'pending', 'payment_tag' => 'Stripe',
            ]);
        }

        return $order;
    }

    /** Invia un evento firmato come fa Stripe (header Stripe-Signature: t=...,v1=HMAC). */
    private function sendEvent(array $event, ?string $secret = self::SECRET)
    {
        $payload = json_encode($event);
        $time = time();
        $signature = hash_hmac('sha256', "$time.$payload", $secret);

        return $this->call('POST', '/webhook/stripe', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t=$time,v1=$signature",
        ], $payload);
    }

    private function event(string $type, array $object): array
    {
        return ['id' => 'evt_test', 'object' => 'event', 'type' => $type, 'data' => ['object' => $object]];
    }

    public function test_payment_intent_succeeded_pays_the_order_and_sends_one_email_per_ticket(): void
    {
        $order = $this->pendingOrder();

        $this->sendEvent($this->event('payment_intent.succeeded', [
            'id' => 'pi_test_123', 'object' => 'payment_intent', 'metadata' => ['group_code' => $order->group_code],
        ]))->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertTrue($order->tickets->every(fn ($t) => $t->status === 'active' && str_starts_with($t->unique_ticket_code, 'TKT_')));
        Queue::assertPushed(SendTicketEmail::class, 2);
    }

    public function test_a_redelivered_event_does_not_send_emails_again_or_change_codes(): void
    {
        $order = $this->pendingOrder();
        $event = $this->event('payment_intent.succeeded', [
            'id' => 'pi_test_123', 'object' => 'payment_intent', 'metadata' => ['group_code' => $order->group_code],
        ]);

        $this->sendEvent($event)->assertOk();
        $codes = $order->tickets()->pluck('unique_ticket_code')->all();
        $this->sendEvent($event)->assertOk();

        $this->assertSame($codes, $order->tickets()->pluck('unique_ticket_code')->all());
        Queue::assertPushed(SendTicketEmail::class, 2);
    }

    public function test_the_order_is_found_by_payment_intent_id_when_metadata_is_missing(): void
    {
        $order = $this->pendingOrder(1);

        $this->sendEvent($this->event('payment_intent.succeeded', [
            'id' => 'pi_test_123', 'object' => 'payment_intent', 'metadata' => [],
        ]))->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_an_order_left_in_payment_error_is_still_paid_when_stripe_confirms(): void
    {
        $order = $this->pendingOrder(1);
        $order->update(['status' => 'payment_error']);

        $this->sendEvent($this->event('payment_intent.succeeded', [
            'id' => 'pi_test_123', 'object' => 'payment_intent', 'metadata' => ['group_code' => $order->group_code],
        ]))->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_a_wrong_signature_is_rejected_and_changes_nothing(): void
    {
        $order = $this->pendingOrder(1);

        $this->sendEvent($this->event('payment_intent.succeeded', [
            'id' => 'pi_test_123', 'object' => 'payment_intent', 'metadata' => ['group_code' => $order->group_code],
        ]), 'whsec_wrong')->assertStatus(400);

        $this->assertSame('pending', $order->fresh()->status);
        Queue::assertNothingPushed();
    }

    public function test_unknown_orders_and_other_events_are_acknowledged(): void
    {
        $this->sendEvent($this->event('payment_intent.succeeded', [
            'id' => 'pi_unknown', 'object' => 'payment_intent', 'metadata' => ['group_code' => 'WFU_NOPE'],
        ]))->assertOk();

        $this->sendEvent($this->event('charge.refunded', ['id' => 'ch_1', 'object' => 'charge']))->assertOk();

        Queue::assertNothingPushed();
    }
}
