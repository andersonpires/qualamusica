<?php
$erro = trim($_GET['erro'] ?? '');
$basePath = app_base_path();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Qual é a música? - Sorteio</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        body { font-family: 'Lexend', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="max-w-3xl mx-auto px-4 py-10">
        <a href="<?php echo $basePath; ?>/" class="inline-flex items-center text-indigo-700 font-semibold hover:underline">
            ← Voltar para o início
        </a>

        <section class="mt-6 bg-white rounded-2xl shadow-lg p-6 md:p-10">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-indigo-900">Gerador de Cartelas para Sorteio</h1>
            <p class="mt-3 text-slate-700 leading-relaxed">
                Informe um número entre <strong>1 e 100</strong>. O sistema gera um PDF A4 com grade de 3 colunas por 3 linhas e quadrados de 6cm,
                contendo os números de <strong>1 até o valor informado</strong>, em negrito e tamanho grande.
            </p>

            <?php if ($erro !== ''): ?>
                <div class="mt-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4 text-red-800" role="alert" aria-live="assertive">
                    <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="post" action="<?php echo $basePath; ?>/sorteio/pdf" target="_blank" rel="noopener" novalidate>
                <div>
                    <label for="quantidade" class="block text-base font-bold text-slate-900">
                        Número máximo para o sorteio
                    </label>
                    <p id="quantidade-hint" class="mt-1 text-sm text-slate-600">
                        Digite um valor inteiro de 1 a 100.
                    </p>
                    <input
                        id="quantidade"
                        name="quantidade"
                        type="number"
                        inputmode="numeric"
                        min="1"
                        max="100"
                        step="1"
                        required
                        aria-describedby="quantidade-hint"
                        class="mt-3 w-full md:w-64 rounded-xl border-2 border-slate-300 px-4 py-3 text-xl font-bold text-slate-900 focus:border-indigo-600 focus:ring-indigo-600"
                    />
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-700 px-6 py-3 text-white text-base font-bold hover:bg-indigo-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                >
                    Gerar PDF
                </button>
            </form>
        </section>
    </main>
</body>
</html>

