<?php
/**
 * VIEW: Tocando - Reprodução do Karaokê (somente áudio)
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
$karaokeVideoId = MusicService::extractYouTubeVideoId($musica->link_karaoke);
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Qual é a música? - Tocando</title>
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
        .animate-pulse-lg {
            animation: pulse-lg 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-lg {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        .playing-indicator {
            animation: playing 1.5s ease-in-out infinite;
        }
        @keyframes playing {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-indigo-700 to-indigo-900 text-white min-h-screen flex flex-col justify-between pb-20">
    <header class="flex justify-between items-center px-6 py-4 absolute top-0 left-0 right-0 z-20">
        <a href="<?php echo $basePath; ?>/" class="flex items-center gap-2 bg-white/20 hover:bg-white/30 rounded-full px-4 py-2 transition-all">
            <span class="material-symbols-outlined">arrow_back</span>
            <span class="hidden sm:inline text-sm font-bold">Voltar</span>
        </a>
        <div class="text-center">
            <p class="text-xs font-light opacity-80">Adivinhando...</p>
        </div>
        <div class="w-10"></div>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center px-6 pt-20">
        <div class="text-center mb-16 animate-pulse">
            <div class="mb-8">
                <span class="material-symbols-outlined text-7xl playing-indicator">music_note</span>
            </div>
            <h1 class="text-5xl md:text-6xl font-extrabold mb-4 tracking-tighter">Qual é a música?</h1>
            <p class="text-2xl font-light opacity-90 mb-2">Ouça com atenção...</p>
            <p class="text-sm font-light opacity-75">Você consegue identificar?</p>
        </div>

        <div class="w-full max-w-2xl mb-12">
            <div class="bg-white/10 border border-white/20 rounded-2xl p-6 shadow-2xl backdrop-blur">
                <?php if ($karaokeVideoId): ?>
                    <div class="flex flex-col items-center text-center gap-5">
                        <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center overflow-hidden">
                            <img
                                src="<?php echo $basePath; ?>/app/assets/img/animacao.gif"
                                alt="Animação de reprodução"
                                class="w-14 h-14 object-contain"
                            />
                        </div>
                        <div>
                            <p class="text-lg font-semibold">Karaokê em reprodução</p>
                            <p class="text-sm opacity-80">Somente áudio. O vídeo fica oculto.</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" id="btn-play-audio" class="bg-yellow-300 text-slate-900 px-5 py-3 rounded-full font-bold flex items-center gap-2 hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined">play_arrow</span>
                                Tocar
                            </button>
                            <button type="button" id="btn-pause-audio" class="bg-white/20 text-white px-5 py-3 rounded-full font-bold flex items-center gap-2 hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined">pause</span>
                                Pausar
                            </button>
                        </div>
                        <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-2">
                            <p class="text-xs opacity-80">Tempo da música</p>
                            <p id="audio-timer" class="text-2xl font-bold tracking-wider">00:00</p>
                        </div>
                        <p id="audio-status" class="text-xs opacity-75">Carregando player de áudio...</p>
                    </div>
                    <div id="youtube-audio-player" style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;" aria-hidden="true"></div>
                <?php else: ?>
                    <div class="w-full h-40 flex items-center justify-center">
                        <div class="text-center">
                            <p class="text-red-200 font-bold mb-2">Erro ao carregar áudio</p>
                            <p class="text-white/80 text-sm">Link do karaokê inválido</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-col gap-4 w-full max-w-sm">
            <a href="<?php echo $basePath; ?>/revelar?id=<?php echo (int)$musica->id; ?>"
               class="bg-yellow-300 text-slate-900 px-8 py-4 rounded-full font-bold text-2xl text-center hover:scale-105 transition-all shadow-xl active:scale-95 flex items-center justify-center gap-2 animate-pulse-lg">
                <span class="material-symbols-outlined text-3xl">visibility</span>
                <span>Revelar Resposta</span>
            </a>

            <a href="<?php echo $basePath; ?>/"
               class="bg-white/20 text-white px-8 py-4 rounded-full font-bold text-lg text-center hover:scale-105 transition-all active:scale-95 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">skip_next</span>
                <span>Próxima Música</span>
            </a>
        </div>
    </main>

    <footer class="text-center pb-8 px-6">
        <p class="text-xs font-light opacity-60">Dica: use o karaokê para ajudar sua memória musical.</p>
    </footer>

    <script>
        const karaokeVideoId = <?php echo json_encode($karaokeVideoId, JSON_UNESCAPED_UNICODE); ?>;
        let ytAudioPlayer = null;
        let ytPlayerReady = false;
        let timerInterval = null;

        function setAudioStatus(text) {
            const el = document.getElementById('audio-status');
            if (el) {
                el.textContent = text;
            }
        }

        function setTimerText(text) {
            const timer = document.getElementById('audio-timer');
            if (timer) {
                timer.textContent = text;
            }
        }

        function formatSeconds(totalSeconds) {
            const safe = Math.max(0, Math.floor(totalSeconds));
            const minutes = Math.floor(safe / 60);
            const seconds = safe % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function refreshTimerFromPlayer() {
            if (!ytAudioPlayer || typeof ytAudioPlayer.getCurrentTime !== 'function') {
                return;
            }
            const currentTime = ytAudioPlayer.getCurrentTime() || 0;
            setTimerText(formatSeconds(currentTime));
        }

        function startTimer() {
            stopTimer();
            refreshTimerFromPlayer();
            timerInterval = setInterval(refreshTimerFromPlayer, 500);
        }

        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        function onYouTubeIframeAPIReady() {
            if (!karaokeVideoId) {
                return;
            }

            ytAudioPlayer = new YT.Player('youtube-audio-player', {
                videoId: karaokeVideoId,
                playerVars: {
                    autoplay: 0,
                    controls: 0,
                    rel: 0,
                    modestbranding: 1,
                    playsinline: 1
                },
                events: {
                    onReady: () => {
                        ytPlayerReady = true;
                        setTimerText('00:00');
                        setAudioStatus('Player pronto. Clique em Tocar.');
                    },
                    onError: () => {
                        setAudioStatus('Não foi possível carregar o áudio deste vídeo.');
                    },
                    onStateChange: (event) => {
                        if (!window.YT || !window.YT.PlayerState) {
                            return;
                        }

                        if (event.data === window.YT.PlayerState.PLAYING) {
                            startTimer();
                            return;
                        }

                        if (
                            event.data === window.YT.PlayerState.PAUSED ||
                            event.data === window.YT.PlayerState.ENDED
                        ) {
                            refreshTimerFromPlayer();
                            stopTimer();
                        }
                    }
                }
            });
        }

        function setupAudioControls() {
            const playBtn = document.getElementById('btn-play-audio');
            const pauseBtn = document.getElementById('btn-pause-audio');

            if (!playBtn || !pauseBtn) {
                return;
            }

            playBtn.addEventListener('click', () => {
                if (!ytPlayerReady || !ytAudioPlayer) {
                    setAudioStatus('Aguarde o player carregar para tocar.');
                    return;
                }
                ytAudioPlayer.playVideo();
                setAudioStatus('Áudio tocando.');
                startTimer();
            });

            pauseBtn.addEventListener('click', () => {
                if (!ytPlayerReady || !ytAudioPlayer) {
                    return;
                }
                ytAudioPlayer.pauseVideo();
                setAudioStatus('Áudio pausado.');
                refreshTimerFromPlayer();
                stopTimer();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setupAudioControls();
        });
    </script>
    <?php if ($karaokeVideoId): ?>
    <script src="https://www.youtube.com/iframe_api"></script>
    <?php endif; ?>
</body>
</html>
