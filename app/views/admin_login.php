<?php
$loginError = $_SESSION['admin_login_error'] ?? null;
$logoutMessage = $_SESSION['admin_login_success'] ?? null;
unset($_SESSION['admin_login_error'], $_SESSION['admin_login_success']);
$basePath = app_base_path();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login Admin - Qual é a música?</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <main class="w-full max-w-md rounded-2xl bg-white shadow-xl p-8">
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Painel Administrativo</h1>
        <p class="text-slate-600 mb-6">Informe a senha para acessar o admin.</p>

        <form action="<?php echo $basePath; ?>/admin" method="POST" class="space-y-4">
            <div>
                <label for="admin_password" class="block text-sm font-medium text-slate-700 mb-2">Senha</label>
                <input
                    id="admin_password"
                    name="admin_password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
            </div>
            <button
                type="submit"
                class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 transition-colors"
            >
                Entrar
            </button>
        </form>

        <a href="<?php echo $basePath; ?>/" class="mt-6 inline-block text-sm text-indigo-700 hover:underline">Voltar para Home</a>
    </main>

    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000
        };

        <?php if ($loginError): ?>
        toastr.error(<?php echo json_encode($loginError, JSON_UNESCAPED_UNICODE); ?>);
        <?php endif; ?>

        <?php if ($logoutMessage): ?>
        toastr.info(<?php echo json_encode($logoutMessage, JSON_UNESCAPED_UNICODE); ?>);
        <?php endif; ?>
    </script>
</body>
</html>

