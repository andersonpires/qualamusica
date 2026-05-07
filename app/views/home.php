<?php
/**
 * VIEW: Home - Seleção de Músicas
 */

try {
    Config::load();
    Database::getConnection();
    $musicas = MusicService::getAllMusics();

    // Ordem estável para a vitrine do jogo: menor ID primeiro.
    usort($musicas, fn($a, $b) => ((int)$a->id <=> (int)$b->id));
} catch (Exception $e) {
    $musicas = [];
}

$nomeApp = Config::get('APP_NAME', 'Qual é a música?');
$basePath = app_base_path();
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($nomeApp); ?> - Home</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "tertiary": "#176a21",
                    "on-secondary-fixed": "#433700",
                    "on-primary-fixed": "#000005",
                    "tertiary-dim": "#025d16",
                    "surface-container-low": "#f0f1f1",
                    "on-secondary-fixed-variant": "#645300",
                    "surface-bright": "#f6f6f6",
                    "tertiary-fixed": "#9df197",
                    "primary": "#4953ac",
                    "secondary-container": "#fdd400",
                    "on-surface-variant": "#5a5c5c",
                    "outline-variant": "#acadad",
                    "surface-container-lowest": "#ffffff",
                    "surface-container-highest": "#dbdddd",
                    "on-secondary-container": "#594a00",
                    "primary-dim": "#3d469f",
                    "on-tertiary-fixed-variant": "#12661e",
                    "on-background": "#2d2f2f",
                    "surface-container": "#e7e8e8",
                    "on-error-container": "#510017",
                    "inverse-surface": "#0c0f0f",
                    "surface-dim": "#d3d5d5",
                    "surface": "#f6f6f6",
                    "on-primary": "#f3f1ff",
                    "secondary": "#6d5a00",
                    "primary-container": "#929bfa",
                    "on-error": "#ffefef",
                    "primary-fixed": "#929bfa",
                    "surface-container-high": "#e1e3e3",
                    "inverse-on-surface": "#9c9d9d",
                    "secondary-fixed": "#fdd400",
                    "error-dim": "#a70138",
                    "surface-tint": "#4953ac",
                    "on-secondary": "#fff2ce",
                    "on-primary-container": "#0b1574",
                    "on-surface": "#2d2f2f",
                    "secondary-fixed-dim": "#edc600",
                    "error-container": "#f74b6d",
                    "secondary-dim": "#5f4e00",
                    "background": "#f6f6f6",
                    "tertiary-fixed-dim": "#90e28a",
                    "primary-fixed-dim": "#858eec",
                    "surface-variant": "#dbdddd",
                    "on-tertiary-container": "#005c15",
                    "inverse-primary": "#929bfa",
                    "outline": "#767777",
                    "on-tertiary-fixed": "#00460e",
                    "on-primary-fixed-variant": "#18217d",
                    "tertiary-container": "#9df197",
                    "error": "#b41340",
                    "on-tertiary": "#d1ffc8"
            },
            "borderRadius": {
                    "DEFAULT": "1rem",
                    "lg": "2rem",
                    "xl": "3rem",
                    "full": "9999px"
            },
            "fontFamily": {
                    "headline": ["Lexend"],
                    "body": ["Lexend"],
                    "label": ["Lexend"]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Lexend', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .sonic-ripple-on-tap:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body class="bg-surface text-on-background min-h-screen pb-32">
<nav class="bg-slate-50 dark:bg-slate-900 flex justify-between items-center w-full px-6 py-4 font-lexend tracking-tight sticky top-0 z-40">
<div class="text-2xl font-bold text-indigo-900 dark:text-indigo-300"><?php echo htmlspecialchars($nomeApp); ?></div>
<div class="flex items-center gap-6">
<button id="btn-admin" class="text-slate-500 dark:text-slate-400 hover:scale-105 transition-transform material-symbols-outlined" title="Painel Administrativo">lock</button>
</div>
</nav>

<main class="max-w-6xl mx-auto px-6 pt-12">
<header class="mb-12 relative">
<div class="absolute -top-10 -left-10 w-64 h-64 bg-primary-container/20 rounded-full blur-3xl -z-10"></div>
<div class="absolute top-20 right-0 w-48 h-48 bg-secondary-container/20 rounded-full blur-3xl -z-10"></div>
<h1 class="text-5xl md:text-7xl font-extrabold text-on-background tracking-tighter mb-4">Qual &eacute; a m&uacute;sica?</h1>
<p class="text-xl md:text-2xl text-on-surface-variant font-light max-w-2xl leading-relaxed">Escolha um desafio e teste seus ouvidos. Cada can&ccedil;&atilde;o &eacute; uma nova chance de brilhar!</p>
</header>

<?php if (empty($musicas)): ?>
<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8 rounded">
<p class="font-bold">Nenhuma m&uacute;sica cadastrada</p>
<p>Acesse o painel admin para adicionar m&uacute;sicas ao cat&aacute;logo.</p>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-12 gap-6 pb-12">
<?php foreach ($musicas as $index => $musica): ?>
<?php
$isFeatured = $index === 0;
$label = 'Música ' . ($index + 1);
$cardClass = $isFeatured
    ? 'md:col-span-8 group cursor-pointer relative overflow-hidden bg-surface-container-lowest rounded-xl shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.05)] transition-all duration-300 hover:scale-[1.02]'
    : 'md:col-span-4 group cursor-pointer rounded-xl p-8 flex flex-col justify-between shadow-lg transition-all duration-300 hover:scale-[1.02] min-h-[300px]';

$bgClass = match($index % 4) {
    0 => 'bg-secondary-container text-on-secondary-container',
    1 => 'bg-tertiary-container text-on-tertiary-container',
    2 => 'bg-primary-container text-on-primary-container',
    default => 'bg-surface-container-highest text-on-surface'
};

$icon = match($index % 4) {
    0 => 'music_note',
    1 => 'piano',
    2 => 'equalizer',
    default => 'radio'
};
?>

<?php if ($isFeatured): ?>
<a href="<?php echo $basePath; ?>/tocando?id=<?php echo (int)$musica->id; ?>" class="<?php echo $cardClass; ?>" title="<?php echo htmlspecialchars($label); ?>">
<div class="absolute inset-0 bg-gradient-to-br from-primary to-primary-dim opacity-90"></div>
<img alt="Visual musical" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-40" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDEXSuN8XyndsbLmsH7emm8L7CgMcmcM_unSWyRTbAzmcHIeHdRo9n7HgP1TxGvPUqnAudsRHhNShP9dsh7q4o_bbnoXKi-_HIhbnpd9qA26SIW7UDX-EjAJo5NsYFHZiyv7gCFle0E81pIOWM0QXHGs41WPce3WlR4Uy9G48xwUvvOq2FDt6BloIo1qOjRp78-Aueint0FCCwqvGjJzpwhlXTaN99zpBpfhWSkVRDxbi_ORdHGw05jkWxY4ntFRKHVnPKwsMau5g"/>
<div class="relative p-10 h-full flex flex-col justify-between min-h-[400px]">
<div>
<span class="bg-secondary-container text-on-secondary-container px-4 py-1 rounded-full text-sm font-bold tracking-widest uppercase">Especial do Dia</span>
<h2 class="text-4xl md:text-6xl font-bold text-white mt-4 tracking-tight"><?php echo htmlspecialchars($label); ?></h2>
</div>
<div class="flex items-center gap-4">
<div class="bg-white text-primary w-20 h-20 rounded-full flex items-center justify-center shadow-xl group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
</div>
<span class="text-white font-bold text-xl">Começar agora</span>
</div>
</div>
</a>
<?php else: ?>
<a href="<?php echo $basePath; ?>/tocando?id=<?php echo (int)$musica->id; ?>" class="<?php echo $cardClass . ' ' . $bgClass; ?>" title="<?php echo htmlspecialchars($label); ?>">
<div>
<div class="w-16 h-16 bg-white/30 rounded-full flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-3xl"><?php echo $icon; ?></span>
</div>
<h2 class="text-3xl font-bold tracking-tight"><?php echo htmlspecialchars($label); ?></h2>
</div>
<div class="flex justify-end">
<div class="bg-white text-current w-16 h-16 rounded-full flex items-center justify-center shadow-md group-hover:rotate-12 transition-transform">
<span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">play_circle</span>
</div>
</div>
</a>
<?php endif; ?>

<?php endforeach; ?>
</div>
</main>

<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 pb-6 pt-3 bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.1)] rounded-t-[3rem] font-lexend font-medium text-xs">
<a class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400 p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full sonic-ripple-on-tap transition-all" href="<?php echo $basePath; ?>/">
<span class="material-symbols-outlined text-2xl mb-1">home</span>
<span>Início</span>
</a>
<a class="flex flex-col items-center justify-center bg-yellow-400 text-indigo-950 rounded-full px-6 py-2 scale-110 sonic-ripple-on-tap transition-all" href="<?php echo $basePath; ?>/">
<span class="material-symbols-outlined text-2xl mb-1" style="font-variation-settings: 'FILL' 1;">music_note</span>
<span class="font-bold">Jogos</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400 p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full sonic-ripple-on-tap transition-all" href="#">
<span class="material-symbols-outlined text-2xl mb-1">library_music</span>
<span>Biblioteca</span>
</a>
<a class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400 p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full sonic-ripple-on-tap transition-all" href="#">
<span class="material-symbols-outlined text-2xl mb-1">person</span>
<span>Perfil</span>
</a>
</nav>

<button onclick="location.href='<?php echo $basePath; ?>/admin'" class="fixed right-6 bottom-32 bg-primary text-white w-20 h-20 rounded-full shadow-2xl flex items-center justify-center hover:scale-105 transition-transform active:scale-95 z-40" title="Painel Admin">
<span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">lock</span>
</button>

<script>
document.getElementById('btn-admin').addEventListener('click', function() {
    window.location.href = '<?php echo $basePath; ?>/admin';
});
</script>
</body></html>

