{{-- Foto dell'edizione 2026 a tutta larghezza nelle pagine interne. $photo = nome in public/images/home, $alt = descrizione --}}
<figure class="page-photo">
    <img src="/images/home/{{ $photo }}-800.jpg" srcset="/images/home/{{ $photo }}-800.jpg 800w, /images/home/{{ $photo }}.jpg 1600w" sizes="(max-width: 1240px) 100vw, 1240px" width="1600" height="1066" alt="{{ $alt }}" loading="lazy">
</figure>
