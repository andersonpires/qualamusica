<?php
/**
 * VIEW: Revelar - Exibição completa da música
 */

try {
    Config::load();
    Database::getConnection();

    $musicId = $_GET['id'] ?? null;

    if (!$musicId || !is_numeric($musicId)) {
        header('Location: ' . app_url('/'));
        exit;
    }

    $musica = MusicService::getMusicById((int)$musicId);

    if (!$musica) {
        header('Location: ' . app_url('/'));
        exit;
    }
} catch (Exception $e) {
    header('Location: ' . app_url('/'));
    exit;
}

$basePath = app_base_path();
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Qual é a música? - Revelado</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Lexend', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        #confetti-container {
            position: fixed;
            inset: 0;
            z-index: 30;
            pointer-events: none;
            overflow: hidden;
        }
        .confetti-piece {
            position: fixed;
            top: -20px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: confetti-fall 2.8s linear forwards;
            will-change: transform, opacity;
        }
        @keyframes confetti-fall {
            to {
                transform: translateY(110vh) rotate(360deg);
                opacity: 0;
            }
        }
        .glow-text {
            text-shadow: 0 0 30px rgba(73, 83, 172, 0.5), 0 0 60px rgba(147, 155, 250, 0.3);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen">
    <div id="confetti-container"></div>

    <header class="bg-white sticky top-0 z-40 shadow-md">
        <div class="max-w-6xl mx-auto flex justify-between items-center px-6 py-4">
            <a href="<?php echo $basePath; ?>/" class="flex items-center gap-2 text-indigo-700 hover:scale-105 transition-transform">
                <span class="material-symbols-outlined">arrow_back</span>
                <span class="hidden sm:inline font-bold">Voltar</span>
            </a>
            <div class="text-center text-xl font-bold text-indigo-700">Resposta Revelada</div>
            <div class="w-10"></div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-12">
        <div class="bg-gradient-to-br from-indigo-700 via-indigo-500 to-indigo-800 rounded-3xl p-0 overflow-hidden shadow-2xl mb-12">
            <div class="w-full bg-black rounded-t-3xl overflow-hidden flex items-center justify-center">
                <?php
                $videoId = MusicService::extractYouTubeVideoId($musica->link_clipe);
                if ($videoId):
                ?>
                    <iframe
                        width="1280"
                        height="720"
                        src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>?rel=0&modestbranding=1"
                        title="Clipe da Música"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        class="w-full h-auto aspect-video max-h-[75vh]">
                    </iframe>
                <?php else: ?>
                    <div class="w-full h-96 flex items-center justify-center bg-slate-300">
                        <div class="text-center">
                            <p class="text-red-700 font-bold mb-2">Erro ao carregar vídeo</p>
                            <p class="text-slate-700 text-sm">Link do clipe inválido</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-8 md:p-12 text-white">
                <div class="text-center mb-8">
                    <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center animate-bounce" style="background-color: #E9CDC2;">
                        <img
                            src="<?php echo $basePath; ?>/app/assets/img/disco.gif"
                            alt="Disco animado"
                            class="w-16 h-16 object-contain"
                        />
                    </div>
                </div>

                <div class="text-center mb-8">
                    <h1 class="text-5xl md:text-6xl font-extrabold mb-4 tracking-tighter glow-text">
                        <?php echo htmlspecialchars($musica->nome); ?>
                    </h1>
                    <p class="text-2xl font-light opacity-95 mb-2"><?php echo htmlspecialchars($musica->cantor); ?></p>
                    <p class="text-lg font-light opacity-80">Composição: <?php echo htmlspecialchars($musica->autor); ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white/10 rounded-2xl p-6 backdrop-blur-sm border border-white/20">
                    <div class="text-center">
                        <p class="text-sm font-light opacity-80 mb-2">Ano de Lançamento</p>
                        <p class="text-4xl font-extrabold"><?php echo htmlspecialchars($musica->ano); ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-light opacity-80 mb-2">Dificuldade</p>
                        <p class="text-2xl">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1; color: #fdd400;">music_note</span>
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1; color: #fdd400;">music_note</span>
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1; color: #fdd400;">music_note</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 max-w-sm mx-auto mb-12">
            <a href="<?php echo $basePath; ?>/" class="bg-indigo-700 text-white px-8 py-4 rounded-full font-bold text-lg text-center hover:scale-105 transition-all shadow-lg flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">home</span>
                <span>Voltar para Home</span>
            </a>
            <a href="<?php echo $basePath; ?>/" class="bg-indigo-200 text-indigo-900 px-8 py-4 rounded-full font-bold text-lg text-center hover:scale-105 transition-all shadow-lg flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">skip_next</span>
                <span>Próxima Música</span>
            </a>
        </div>

        <div class="bg-yellow-300 text-slate-900 rounded-2xl p-8 max-w-2xl mx-auto shadow-lg">
            <div class="flex items-start gap-4 mb-4">
                <span class="material-symbols-outlined text-3xl flex-shrink-0">info</span>
                <div>
                    <h3 class="text-xl font-bold mb-2">Curiosidade</h3>
                    <p class="font-light">
                        <?php
                        $facts = [
                            'A música foi lançada em ' . $musica->ano . '.',
                            htmlspecialchars($musica->cantor) . ' é um talento incrível.',
                            'Esta composição é de ' . htmlspecialchars($musica->autor) . '.',
                            'Você conseguiu adivinhar?'
                        ];
                        echo $facts[array_rand($facts)];
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center py-12 px-6 text-slate-600">
        <p class="text-sm font-light">Divirta-se explorando o catálogo de músicas.</p>
    </footer>

    <script>
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#fdd400', '#4953ac', '#929bfa', '#176a21', '#f74b6d'];

            for (let i = 0; i < 28; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti-piece';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = Math.random() * 8 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                confetti.style.animationDelay = Math.random() * 0.25 + 's';
                container.appendChild(confetti);

                setTimeout(() => confetti.remove(), 3200);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            createConfetti();
        });
    </script>
</body>
</html>
