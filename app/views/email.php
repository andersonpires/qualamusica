<?php
$nomeApp = Config::get('APP_NAME', 'Qual é a música?');
$flashSuccess = $_SESSION['email_flash_success'] ?? null;
$flashError = $_SESSION['email_flash_error'] ?? null;
$lastInput = $_SESSION['email_flash_input'] ?? '';
unset($_SESSION['email_flash_success'], $_SESSION['email_flash_error'], $_SESSION['email_flash_input']);
$basePath = app_base_path();
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($nomeApp); ?> - Enviar Convite</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#4953ac',
                        tertiary: '#176a21',
                        background: '#f6f6f6',
                        surface: '#ffffff',
                        secondary: '#fdd400',
                        outline: '#767777'
                    }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'Lexend', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background text-slate-900 min-h-screen">
    <nav class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <span class="text-2xl font-extrabold text-primary tracking-tight"><?php echo htmlspecialchars($nomeApp); ?></span>
            <a href="<?php echo $basePath; ?>/" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-primary">
                <span class="material-symbols-outlined">arrow_back</span>
                Voltar
            </a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-12">
        <section class="mb-10">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-3">Enviar Convite por E-mail</h1>
            <p class="text-lg text-slate-600 max-w-3xl">
                Envie um convite gentil para testar o app e conhecer a proposta de estímulo e reabilitação cognitiva.
            </p>
        </section>

        <?php if ($flashSuccess): ?>
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-5 py-4">
                <?php echo htmlspecialchars($flashSuccess); ?>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 text-red-800 px-5 py-4">
                <?php echo htmlspecialchars($flashError); ?>
            </div>
        <?php endif; ?>

        <section class="bg-surface rounded-2xl shadow-xl border border-slate-200 p-6 md:p-8">
            <form method="POST" action="<?php echo $basePath; ?>/email" class="space-y-5">
                <div>
                    <label for="recipient_email" class="block text-sm font-bold text-slate-700 mb-2">
                        E-mail do destinatário
                    </label>
                    <input
                        id="recipient_email"
                        name="recipient_email"
                        type="text"
                        required
                        value="<?php echo htmlspecialchars($lastInput); ?>"
                        placeholder="exemplo@dominio.com"
                        class="w-full rounded-xl border border-slate-300 px-4 py-4 text-base focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    />
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-white px-6 py-4 font-bold hover:scale-[1.01] active:scale-[0.99] transition-transform"
                >
                    <span class="material-symbols-outlined">send</span>
                    Enviar Convite
                </button>
            </form>
        </section>
    </main>
</body>
</html>

