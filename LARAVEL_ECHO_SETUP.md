# Laravel Echo Server configuration

# Jalankan perintah berikut di terminal (Node.js harus terinstall):

#

npm install -g laravel-echo-server
npm install socket.io redis

#

# Setelah itu jalankan:

# laravel-echo-server init

#

# Ikuti instruksi, pastikan host, port, dan redis sudah sesuai.

# Untuk menjalankan server:

# laravel-echo-server start

#

# Pastikan di .env Laravel:

# BROADCAST_DRIVER=redis

#

# Pastikan Redis sudah berjalan di server Anda.
