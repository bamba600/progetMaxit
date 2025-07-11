<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXIT SA - Tableau de Bord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-blue-600 to-blue-800 min-h-screen shadow-xl">
            <div class="p-6">
                <!-- Menu Items -->
                <div class="space-y-3">
                    <div class="bg-blue-700 hover:bg-blue-600 rounded-lg p-4 cursor-pointer transition-all duration-200 transform hover:scale-105">
                        <span class="text-white font-medium">Tableau de bord</span>
                    </div>
                    <div class="bg-blue-700 hover:bg-blue-600 rounded-lg p-4 cursor-pointer transition-all duration-200 transform hover:scale-105">
                        <span class="text-white font-medium">Paiement</span>
                    </div>
                    <div class="bg-blue-700 hover:bg-blue-600 rounded-lg p-4 cursor-pointer transition-all duration-200 transform hover:scale-105">
                        <span class="text-white font-medium">Transfert</span>
                    </div>
                    <div class="bg-blue-700 hover:bg-blue-600 rounded-lg p-4 cursor-pointer transition-all duration-200 transform hover:scale-105">
                        <span class="text-white font-medium">Comptes</span>
                    </div>
                    <a href="/deconnexion" class="block mt-8">
                        <div class="bg-red-600 hover:bg-red-700 rounded-lg p-4 cursor-pointer transition-all duration-200 transform hover:scale-105">
                            <span class="text-white font-medium">Déconnexion</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    Bonjour <?= htmlspecialchars($utilisateur->getNomComplet() ?? 'Utilisateur') ?>
                </h1>
            </div>

            <!-- Compte Principal Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-8 mb-8 shadow-xl">
                <h2 class="text-2xl font-bold text-white mb-4">Compte Principal</h2>
                <div class="text-4xl font-bold text-white">
                    <?= number_format($comptePrincipal ? $comptePrincipal->getSolde() : 0, 2, ',', ' ') ?> <span class="text-xl">FCFA</span>
                </div>
            </div>

            <!-- Transactions Section -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">10 dernières transactions</h3>
                
                <?php if (empty($dernieresTransactions)): ?>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-600 text-lg">Aucune transaction pour le moment</p>
                        <p class="text-gray-500 text-sm">Vos transactions apparaîtront ici</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($dernieresTransactions as $transaction): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-4">
                                    <?php
                                    $isCredit = in_array($transaction->getType(), ['depot']);
                                    $iconColor = $isCredit ? 'green' : 'red';
                                    ?>
                                    <div class="w-12 h-12 bg-<?= $iconColor ?>-100 rounded-full flex items-center justify-center">
                                        <?php if ($isCredit): ?>
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-500"><?= $transaction->getDate()->format('d/m/Y') ?></span>
                                            <span class="text-gray-800 font-medium"><?= htmlspecialchars($transaction->getDescription() ?: ucfirst($transaction->getType())) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-<?= $iconColor ?>-600 font-semibold">
                                    <?= ($isCredit ? '+' : '-') . number_format($transaction->getMontant(), 0, ',', ' ') ?> FCFA
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Voir toutes les transactions -->
            <div class="text-center">
                <a href="#" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <span>Voir toutes les transactions</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</body>
</html>