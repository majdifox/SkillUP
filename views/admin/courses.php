<?php

ob_start();

?>

<body class="flex flex-col min-h-screen bg-gray-100">
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-4">Catalogue des cours</h1>
            <div class="flex gap-4 mb-6">
                <input type="search" 
                       placeholder="Rechercher un cours..." 
                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                <button class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                    Rechercher
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Course Card -->
            <a href="/cours-detail.php" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <img src="https://via.placeholder.com/400x200" alt="Course thumbnail" class="w-full h-48 object-cover">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">Introduction au développement web</h3>
                    <p class="text-gray-600 mb-4">Apprenez les bases du développement web avec HTML, CSS et JavaScript.</p>
                    <div class="flex justify-between items-center">
                        <span class="text-red-600 font-semibold">Gratuit</span>
                        <span class="text-sm text-gray-500">12 heures</span>
                    </div>
                </div>
            </a>
            <!-- Repeat course cards -->
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-center gap-2">
            <button class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition-colors">&laquo; Précédent</button>
            <button class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors">1</button>
            <button class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition-colors">2</button>
            <button class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition-colors">3</button>
            <button class="px-4 py-2 rounded-md bg-gray-200 hover:bg-gray-300 transition-colors">Suivant &raquo;</button>
        </div>
    </main>

</body>

<?php
$content = ob_get_clean();
require_once("views/layout.php");