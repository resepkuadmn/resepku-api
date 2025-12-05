<html>
  <body>
    <h2>{{ strtoupper($type) }} Baru: {{ $item->judul }}</h2>
    <p>Halo,</p>
    <p>Admin baru saja menambahkan {{ $type }} baru di Resepku:</p>
    <p><strong>{{ $item->judul }}</strong></p>
    <p>Kunjungi aplikasi untuk melihat detail: <a href="http://127.0.0.1:5173/">Buka Resepku</a></p>
    <p>Salam,<br/>Tim Resepku</p>
  </body>
</html>