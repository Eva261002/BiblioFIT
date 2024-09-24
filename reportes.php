 <!--consultar libros prestados sin devolucion---libros pendientes  -->
 <!-- prestamo de libros por carrera -->
 <!-- reporte de asistencia de estudiantes(por carrera) -->
   <!-- reportes de prestamo por periodos ----en rango de fecha -->
   <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema de Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Encabezado -->
    <header class="bg-blue-600 shadow">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <a href="#" class="text-white text-2xl font-bold">Sistema de Biblioteca</a>
            </div>
        </nav>
    </header>

    <!-- Sección de Reportes -->
    <section class="container mx-auto px-6 py-12">
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-8">Generar Reportes</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            <!-- Reporte de Asistencia -->
            <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <h3 class="text-xl font-semibold mb-2">Reporte de Asistencia</h3>
                <p class="text-center text-gray-600 mb-4">Asistencia de estudiantes por carrera</p>
                <button onclick="location.href='reporte_asistencia.php'" class="bg-blue-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition">Generar Reporte</button>
            </div>

            <!-- Reporte de Libros Prestados -->
            <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <h3 class="text-xl font-semibold mb-2">Libros Prestados</h3>
                <p class="text-center text-gray-600 mb-4">Listado de libros que fueron prestados</p>
                <button onclick="location.href='reporte_libros_prestados.php'" class="bg-blue-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition">Generar Reporte</button>
            </div>

            <!-- Préstamos por Carrera -->
            <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <h3 class="text-xl font-semibold mb-2">Préstamos por Carrera</h3>
                <p class="text-center text-gray-600 mb-4">Préstamos de libros por carrera</p>
                <button onclick="location.href='reporte_prestamos_por_carrera.php'" class="bg-blue-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition">Generar Reporte</button>
            </div>

            <!-- Reporte por Periodo -->
            <div class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center hover:shadow-2xl transition transform hover:-translate-y-2">
                <h3 class="text-xl font-semibold mb-2">Reportes por Periodo</h3>
                <p class="text-center text-gray-600 mb-4">Selecciona rango de fechas</p>
                <button onclick="location.href='reporte_por_periodo.php'" class="bg-blue-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-blue-700 transition">Generar Reporte</button>
            </div>
        </div>
    </section>

    <!-- Pie de Página -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            &copy; 2024 Sistema de Biblioteca - FIT-UABJB. Todos los derechos reservados.
        </div>
    </footer>
</body>
</html>
