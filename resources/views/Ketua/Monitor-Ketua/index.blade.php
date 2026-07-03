<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Ketua</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    @include('Ketua.Monitor-Ketua.header')

    <!-- Main Content -->
    <main class="flex-1 p-6 flex flex-col">
        <div class="bg-white flex-1 shadow-md border border-gray-200 flex flex-col p-6">
            
            <!-- Peserta -->
            @include('Ketua.Monitor-Ketua.peserta')

            <!-- Grid Content -->
            <div class="grid grid-cols-5 gap-4 flex-1 items-start">
                
                <!-- Table Kiri -->
                <div class="col-span-4 overflow-hidden">
                    @include('Ketua.Monitor-Ketua.score-table')
                </div>

                <!-- Panel Kanan -->
                <div class="col-span-1">
                    @include('Ketua.Monitor-Ketua.right-panel')
                </div>

            </div>

        </div>
    </main>

</body>
</html>
