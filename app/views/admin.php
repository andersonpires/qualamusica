<?php
/**
 * VIEW: Admin - Gerenciamento de Músicas
 */
try {
    Config::load();
    Database::getConnection();
    $musicas = MusicService::getAllMusics();
} catch (Exception $e) {
    $musicas = [];
}

$homeOrderMap = [];
$basePath = app_base_path();
$sortedById = $musicas;
usort($sortedById, fn($a, $b) => ((int)$a->id <=> (int)$b->id));
foreach ($sortedById as $idx => $music) {
    $homeOrderMap[(int)$music->id] = $idx + 1;
}
?>
<!DOCTYPE html>
<html class="light" lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Qual é a música? - Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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
                        error: '#b41340',
                        outline: '#767777'
                    }
                }
            }
        };
    </script>

    <style>
        body { font-family: 'Lexend', sans-serif; }
        .loading { opacity: 0.65; pointer-events: none; }
    </style>
</head>
<body class="bg-background text-slate-900 min-h-screen">
    <aside class="h-screen w-64 fixed left-0 top-0 bg-white shadow-xl flex-col z-50 hidden md:flex">
        <div class="pt-8 px-6 pb-6">
            <h2 class="font-bold text-primary text-xl">Admin Panel</h2>
            <p class="text-xs text-slate-500">Qual é a música?</p>
        </div>
        <nav class="flex flex-col h-full pt-4">
            <a class="text-slate-600 py-3 px-6 hover:bg-slate-50 transition-colors font-medium text-sm flex items-center gap-3" href="<?php echo $basePath; ?>/">
                <span class="material-symbols-outlined">arrow_back</span>
                Voltar para Home
            </a>
            <a class="bg-emerald-100 text-primary font-bold rounded-r-full py-3 px-6 text-sm flex items-center gap-3" href="<?php echo $basePath; ?>/admin">
                <span class="material-symbols-outlined">library_music</span>
                Cadastro de Músicas
            </a>
        </nav>
    </aside>

    <main class="md:ml-64 min-h-screen flex flex-col">
        <header class="bg-white sticky top-0 z-40 shadow-md">
            <div class="flex justify-between items-center px-8 py-4">
                <span class="text-2xl font-extrabold text-primary tracking-tight">Qual é a música?</span>
                <button onclick="location.href='<?php echo $basePath; ?>/admin/logout'" class="material-symbols-outlined p-2 text-primary hover:scale-105 transition-transform" title="Sair">logout</button>
            </div>
        </header>

        <div class="p-8 max-w-7xl mx-auto w-full">
            <section class="mb-10">
                <h1 class="text-4xl font-extrabold mb-2 tracking-tight">Cadastro de Músicas</h1>
                <p class="text-lg text-slate-600 font-light">Adicione múltiplas músicas sem recarregar a página.</p>
            </section>

            <div class="bg-surface rounded-lg p-8 shadow-lg mb-10">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_circle</span>
                    Nova Música
                </h2>

                <div class="hidden lg:grid grid-cols-[2fr_1.5fr_1.5fr_2fr_2fr_0.8fr_48px] gap-4 mb-4 px-4 text-sm font-bold text-slate-600 uppercase tracking-wider pb-4 border-b border-slate-200">
                    <div>Nome</div>
                    <div>Cantor</div>
                    <div>Autor</div>
                    <div>Link Karaokê</div>
                    <div>Link Clipe</div>
                    <div>Ano</div>
                    <div></div>
                </div>

                <div class="space-y-4" id="form-rows">
                    <div class="input-row grid grid-cols-1 lg:grid-cols-[2fr_1.5fr_1.5fr_2fr_2fr_0.8fr_48px] gap-4 items-end bg-slate-50 p-4 rounded-xl hover:bg-slate-100 transition-colors">
                        <input class="input-nome w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary py-3 px-4" placeholder="Nome da Música" type="text"/>
                        <input class="input-cantor w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary py-3 px-4" placeholder="Cantor/Artista" type="text"/>
                        <input class="input-autor w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary py-3 px-4" placeholder="Autor/Compositor" type="text"/>
                        <input class="input-link-karaoke w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary py-3 px-4 text-sm" placeholder="URL do YouTube (karaokê)" type="url"/>
                        <input class="input-link-clipe w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary py-3 px-4 text-sm" placeholder="URL do YouTube (clipe)" type="url"/>
                        <input class="input-ano w-full border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary py-3 px-4" placeholder="Ano" type="number" min="1800" max="<?php echo date('Y'); ?>"/>
                        <button type="button" onclick="removeRow(this)" class="bg-red-100 text-error rounded-lg p-3 hover:bg-red-200 transition-colors" title="Remover linha">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </div>

                <div class="mt-8 flex gap-4 flex-wrap">
                    <button onclick="addRow()" class="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg font-bold hover:scale-105 transition-all">
                        <span class="material-symbols-outlined">add</span>
                        Adicionar Linha
                    </button>
                    <button onclick="saveAll()" class="flex items-center gap-2 px-6 py-3 bg-tertiary text-white rounded-lg font-bold hover:scale-105 transition-all" id="btn-save">
                        <span class="material-symbols-outlined">save</span>
                        Salvar Todos
                    </button>
                    <button onclick="clearForm()" class="flex items-center gap-2 px-6 py-3 bg-slate-200 text-slate-900 rounded-lg font-bold hover:scale-105 transition-all">
                        <span class="material-symbols-outlined">clear_all</span>
                        Limpar
                    </button>
                </div>
            </div>

            <div class="bg-surface rounded-lg p-8 shadow-lg mb-10">
                <h2 class="text-2xl font-bold mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">upload_file</span>
                    Importar via CSV
                </h2>
                <p class="text-slate-600 mb-6">
                    Baixe o modelo, preencha as linhas e valide o arquivo antes de importar.
                </p>

                <div class="flex flex-wrap gap-3 mb-6">
                    <a
                        href="<?php echo $basePath; ?>/app/assets/csv/modelo_importacao_musicas.csv"
                        download
                        class="inline-flex items-center gap-2 px-4 py-3 rounded-lg bg-slate-200 text-slate-900 font-semibold hover:bg-slate-300 transition-colors"
                    >
                        <span class="material-symbols-outlined">download</span>
                        Baixar Modelo CSV
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_auto] gap-3 items-start">
                    <input
                        id="csv-file"
                        type="file"
                        accept=".csv,text/csv"
                        class="w-full border border-slate-300 rounded-lg px-4 py-3 bg-white"
                    />
                    <button
                        id="btn-validate-csv"
                        type="button"
                        class="px-5 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-colors"
                    >
                        Validar CSV
                    </button>
                    <button
                        id="btn-import-csv"
                        type="button"
                        disabled
                        class="px-5 py-3 rounded-lg bg-emerald-600 text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed hover:bg-emerald-700 transition-colors"
                    >
                        Importar CSV
                    </button>
                </div>

                <div id="csv-feedback" class="mt-5 hidden rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p id="csv-feedback-summary" class="font-medium text-slate-800"></p>
                    <ul id="csv-feedback-errors" class="mt-3 space-y-2 text-sm text-red-700 list-disc pl-5"></ul>
                </div>
            </div>

            <div class="bg-surface rounded-lg p-8 shadow-lg">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-tertiary">library_music</span>
                    Músicas Cadastradas (<span id="music-count"><?php echo count($musicas); ?></span>)
                </h2>

                <div id="empty-state" class="bg-indigo-50 border-l-4 border-primary p-6 rounded-lg text-center <?php echo empty($musicas) ? '' : 'hidden'; ?>">
                    <p class="font-bold text-lg">Nenhuma música cadastrada</p>
                    <p class="text-slate-600">Comece preenchendo o formulário acima para adicionar músicas.</p>
                </div>

                <div id="desktop-list" class="hidden lg:block overflow-x-auto <?php echo empty($musicas) ? 'hidden' : ''; ?>">
                    <table class="w-full">
                        <thead class="border-b-2 border-slate-200 bg-slate-100">
                            <tr>
                                <th class="text-center p-4 font-bold text-slate-600">Ordem Home</th>
                                <th class="text-left p-4 font-bold text-slate-600">Nome</th>
                                <th class="text-left p-4 font-bold text-slate-600">Cantor</th>
                                <th class="text-left p-4 font-bold text-slate-600">Autor</th>
                                <th class="text-center p-4 font-bold text-slate-600">Ano</th>
                                <th class="text-center p-4 font-bold text-slate-600">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="music-table-body">
                            <?php foreach ($musicas as $musica): ?>
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors" data-music-id="<?php echo $musica->id; ?>">
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm">
                                        <?php echo (int)($homeOrderMap[(int)$musica->id] ?? 0); ?>
                                    </span>
                                </td>
                                <td class="p-4"><strong><?php echo htmlspecialchars($musica->nome); ?></strong></td>
                                <td class="p-4 text-slate-600"><?php echo htmlspecialchars($musica->cantor); ?></td>
                                <td class="p-4 text-slate-600"><?php echo htmlspecialchars($musica->autor); ?></td>
                                <td class="p-4 text-center text-slate-600"><?php echo $musica->ano; ?></td>
                                <td class="p-4 text-center flex justify-center gap-2">
                                    <button onclick="editMusic(<?php echo $musica->id; ?>)" class="p-2 hover:bg-indigo-100 rounded-lg transition-colors" title="Editar">
                                        <span class="material-symbols-outlined text-primary">edit</span>
                                    </button>
                                    <button onclick="deleteMusic(<?php echo $musica->id; ?>)" class="p-2 hover:bg-red-100 rounded-lg transition-colors" title="Deletar">
                                        <span class="material-symbols-outlined text-error">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div id="mobile-list" class="lg:hidden space-y-4 <?php echo empty($musicas) ? 'hidden' : ''; ?>">
                    <?php foreach ($musicas as $musica): ?>
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200" data-music-id="<?php echo $musica->id; ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold"><?php echo htmlspecialchars($musica->nome); ?></h3>
                                <p class="text-sm text-slate-600"><?php echo htmlspecialchars($musica->cantor); ?></p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs mb-1">
                                    #<?php echo (int)($homeOrderMap[(int)$musica->id] ?? 0); ?>
                                </span>
                                <span class="block text-slate-600 text-sm"><?php echo $musica->ano; ?></span>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 mb-4">Autor: <?php echo htmlspecialchars($musica->autor); ?></p>
                        <div class="flex gap-2">
                            <button onclick="editMusic(<?php echo $musica->id; ?>)" class="flex-1 p-2 bg-indigo-100 text-primary rounded-lg text-sm font-bold hover:bg-indigo-200 transition-colors flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-lg">edit</span>
                                Editar
                            </button>
                            <button onclick="deleteMusic(<?php echo $musica->id; ?>)" class="flex-1 p-2 bg-red-100 text-error rounded-lg text-sm font-bold hover:bg-red-200 transition-colors flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-lg">delete</span>
                                Deletar
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <div id="edit-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-[80]">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-slate-900">Editar Música</h3>
                <button type="button" id="edit-cancel-top" class="text-slate-500 hover:text-slate-800">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="edit-form" class="space-y-4">
                <input type="hidden" id="edit-id"/>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit-nome" class="block text-sm font-medium text-slate-700 mb-1">Nome</label>
                        <input id="edit-nome" type="text" class="w-full border border-slate-300 rounded-lg px-4 py-3" required/>
                    </div>
                    <div>
                        <label for="edit-cantor" class="block text-sm font-medium text-slate-700 mb-1">Cantor</label>
                        <input id="edit-cantor" type="text" class="w-full border border-slate-300 rounded-lg px-4 py-3" required/>
                    </div>
                    <div>
                        <label for="edit-autor" class="block text-sm font-medium text-slate-700 mb-1">Autor</label>
                        <input id="edit-autor" type="text" class="w-full border border-slate-300 rounded-lg px-4 py-3" required/>
                    </div>
                    <div>
                        <label for="edit-ano" class="block text-sm font-medium text-slate-700 mb-1">Ano</label>
                        <input id="edit-ano" type="number" min="1800" max="<?php echo date('Y'); ?>" class="w-full border border-slate-300 rounded-lg px-4 py-3" required/>
                    </div>
                    <div class="md:col-span-2">
                        <label for="edit-link-karaoke" class="block text-sm font-medium text-slate-700 mb-1">Link Karaokê</label>
                        <input id="edit-link-karaoke" type="url" class="w-full border border-slate-300 rounded-lg px-4 py-3" required/>
                    </div>
                    <div class="md:col-span-2">
                        <label for="edit-link-clipe" class="block text-sm font-medium text-slate-700 mb-1">Link Clipe</label>
                        <input id="edit-link-clipe" type="url" class="w-full border border-slate-300 rounded-lg px-4 py-3" required/>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" id="edit-cancel" class="px-5 py-3 bg-slate-200 text-slate-900 rounded-lg font-semibold">
                        Cancelar
                    </button>
                    <button type="submit" id="edit-save" class="px-5 py-3 bg-primary text-white rounded-lg font-semibold">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const state = {
            songs: <?php echo json_encode(array_map(fn($m) => $m->toArray(), $musicas), JSON_UNESCAPED_UNICODE); ?>
        };
        let currentEditingId = null;
        let csvValidationReady = false;

        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toast-bottom-right',
            preventDuplicates: false,
            showDuration: 300,
            hideDuration: 1000,
            timeOut: 5000,
            extendedTimeOut: 1000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = String(value ?? '');
            return div.innerHTML;
        }

        function renderSongs() {
            const empty = document.getElementById('empty-state');
            const desktop = document.getElementById('desktop-list');
            const mobile = document.getElementById('mobile-list');
            const tableBody = document.getElementById('music-table-body');
            const count = document.getElementById('music-count');
            const homeOrderMap = new Map(
                [...state.songs]
                    .sort((a, b) => Number(a.id) - Number(b.id))
                    .map((song, idx) => [Number(song.id), idx + 1])
            );

            count.textContent = state.songs.length;

            if (state.songs.length === 0) {
                empty.classList.remove('hidden');
                desktop.classList.add('hidden');
                mobile.classList.add('hidden');
                tableBody.innerHTML = '';
                mobile.innerHTML = '';
                return;
            }

            empty.classList.add('hidden');
            desktop.classList.remove('hidden');
            mobile.classList.remove('hidden');

            tableBody.innerHTML = state.songs.map((music) => `
                <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors" data-music-id="${music.id}">
                    <td class="p-4 text-center">
                        <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm">
                            ${homeOrderMap.get(Number(music.id)) ?? '-'}
                        </span>
                    </td>
                    <td class="p-4"><strong>${escapeHtml(music.nome)}</strong></td>
                    <td class="p-4 text-slate-600">${escapeHtml(music.cantor)}</td>
                    <td class="p-4 text-slate-600">${escapeHtml(music.autor)}</td>
                    <td class="p-4 text-center text-slate-600">${escapeHtml(music.ano)}</td>
                    <td class="p-4 text-center flex justify-center gap-2">
                        <button onclick="editMusic(${music.id})" class="p-2 hover:bg-indigo-100 rounded-lg transition-colors" title="Editar">
                            <span class="material-symbols-outlined text-primary">edit</span>
                        </button>
                        <button onclick="deleteMusic(${music.id})" class="p-2 hover:bg-red-100 rounded-lg transition-colors" title="Deletar">
                            <span class="material-symbols-outlined text-error">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');

            mobile.innerHTML = state.songs.map((music) => `
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200" data-music-id="${music.id}">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold">${escapeHtml(music.nome)}</h3>
                            <p class="text-sm text-slate-600">${escapeHtml(music.cantor)}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs mb-1">
                                #${homeOrderMap.get(Number(music.id)) ?? '-'}
                            </span>
                            <span class="block text-slate-600 text-sm">${escapeHtml(music.ano)}</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">Autor: ${escapeHtml(music.autor)}</p>
                    <div class="flex gap-2">
                        <button onclick="editMusic(${music.id})" class="flex-1 p-2 bg-indigo-100 text-primary rounded-lg text-sm font-bold hover:bg-indigo-200 transition-colors flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-lg">edit</span>
                            Editar
                        </button>
                        <button onclick="deleteMusic(${music.id})" class="flex-1 p-2 bg-red-100 text-error rounded-lg text-sm font-bold hover:bg-red-200 transition-colors flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-lg">delete</span>
                            Deletar
                        </button>
                    </div>
                </div>
            `).join('');
        }

        async function refreshSongsFromApi() {
            const response = await fetch('<?php echo $basePath; ?>/api/musicas');
            const data = await response.json();

            if (response.ok && data.success && Array.isArray(data.data)) {
                state.songs = data.data;
                renderSongs();
            }
        }

        function addRow() {
            const template = document.querySelector('.input-row');
            const newRow = template.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            document.getElementById('form-rows').appendChild(newRow);
        }

        function removeRow(button) {
            const rows = document.querySelectorAll('.input-row');
            if (rows.length > 1) {
                button.closest('.input-row').remove();
                toastr.info('Linha removida');
            } else {
                toastr.warning('Você deve manter pelo menos uma linha');
            }
        }

        function clearForm() {
            document.querySelectorAll('.input-row').forEach((row, idx) => {
                if (idx === 0) {
                    row.querySelectorAll('input').forEach(input => input.value = '');
                } else {
                    row.remove();
                }
            });
            toastr.info('Formulário limpo');
        }

        function getSongById(musicId) {
            return state.songs.find((song) => Number(song.id) === Number(musicId)) || null;
        }

        function openEditModal(music) {
            currentEditingId = Number(music.id);
            document.getElementById('edit-id').value = String(music.id);
            document.getElementById('edit-nome').value = music.nome ?? '';
            document.getElementById('edit-cantor').value = music.cantor ?? '';
            document.getElementById('edit-autor').value = music.autor ?? '';
            document.getElementById('edit-link-karaoke').value = music.link_karaoke ?? '';
            document.getElementById('edit-link-clipe').value = music.link_clipe ?? '';
            document.getElementById('edit-ano').value = music.ano ?? '';

            const modal = document.getElementById('edit-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            currentEditingId = null;
            const modal = document.getElementById('edit-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function buildEditPayload() {
            const nome = document.getElementById('edit-nome').value.trim();
            const cantor = document.getElementById('edit-cantor').value.trim();
            const autor = document.getElementById('edit-autor').value.trim();
            const link_karaoke = document.getElementById('edit-link-karaoke').value.trim();
            const link_clipe = document.getElementById('edit-link-clipe').value.trim();
            const ano = Number.parseInt(document.getElementById('edit-ano').value.trim(), 10);

            if (!nome || !cantor || !autor || !link_karaoke || !link_clipe || Number.isNaN(ano)) {
                return { valid: false, payload: null };
            }

            return {
                valid: true,
                payload: { nome, cantor, autor, link_karaoke, link_clipe, ano }
            };
        }

        function readRowsData() {
            const rows = document.querySelectorAll('.input-row');
            const musicas = [];
            let hasErrors = false;

            rows.forEach((row, idx) => {
                const nome = row.querySelector('.input-nome').value.trim();
                const cantor = row.querySelector('.input-cantor').value.trim();
                const autor = row.querySelector('.input-autor').value.trim();
                const link_karaoke = row.querySelector('.input-link-karaoke').value.trim();
                const link_clipe = row.querySelector('.input-link-clipe').value.trim();
                const ano = row.querySelector('.input-ano').value.trim();

                const hasAny = nome || cantor || autor || link_karaoke || link_clipe || ano;

                if (!hasAny) {
                    return;
                }

                if (!nome || !cantor || !autor || !link_karaoke || !link_clipe || !ano) {
                    toastr.error(`Linha ${idx + 1}: preencha todos os campos.`);
                    hasErrors = true;
                    return;
                }

                musicas.push({
                    nome,
                    cantor,
                    autor,
                    link_karaoke,
                    link_clipe,
                    ano: parseInt(ano, 10)
                });
            });

            return { musicas, hasErrors };
        }

        async function saveAll() {
            const saveBtn = document.getElementById('btn-save');
            const { musicas, hasErrors } = readRowsData();

            if (hasErrors) return;
            if (musicas.length === 0) {
                toastr.warning('Nenhuma linha preenchida para salvar.');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.classList.add('loading');

            let successCount = 0;

            try {
                for (const musica of musicas) {
                    const response = await fetch('<?php echo $basePath; ?>/api/musicas', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(musica)
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        if (data.errors) {
                            Object.values(data.errors).forEach(msg => toastr.error(String(msg)));
                        } else {
                            toastr.error(data.message || 'Erro ao salvar música.');
                        }
                        continue;
                    }

                    successCount += 1;
                    state.songs.unshift(data.data);
                    toastr.success(`"${musica.nome}" cadastrada com sucesso.`);
                }

                renderSongs();

                if (successCount > 0) {
                    clearForm();
                }
            } catch (error) {
                toastr.error(`Erro de conexão: ${error.message}`);
            } finally {
                saveBtn.disabled = false;
                saveBtn.classList.remove('loading');
            }
        }

        function resetCsvValidationState() {
            csvValidationReady = false;
            const importBtn = document.getElementById('btn-import-csv');
            importBtn.disabled = true;
        }

        function renderCsvFeedback(summaryText, errors = []) {
            const wrapper = document.getElementById('csv-feedback');
            const summary = document.getElementById('csv-feedback-summary');
            const list = document.getElementById('csv-feedback-errors');

            wrapper.classList.remove('hidden');
            summary.textContent = summaryText;
            list.innerHTML = '';

            errors.forEach((errorItem) => {
                const li = document.createElement('li');

                if (typeof errorItem === 'string') {
                    li.textContent = errorItem;
                    list.appendChild(li);
                    return;
                }

                const lineText = errorItem.line ? `Linha ${errorItem.line}: ` : '';
                const details = errorItem.errors && typeof errorItem.errors === 'object'
                    ? Object.values(errorItem.errors).join(' | ')
                    : JSON.stringify(errorItem);
                li.textContent = `${lineText}${details}`;
                list.appendChild(li);
            });
        }

        async function validateCsvFile() {
            const fileInput = document.getElementById('csv-file');
            const validateBtn = document.getElementById('btn-validate-csv');
            const importBtn = document.getElementById('btn-import-csv');

            if (!fileInput.files || fileInput.files.length === 0) {
                toastr.warning('Selecione um arquivo CSV para validar.');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', fileInput.files[0]);

            validateBtn.disabled = true;
            validateBtn.classList.add('loading');
            resetCsvValidationState();

            try {
                const response = await fetch('<?php echo $basePath; ?>/api/musicas/import-csv/validate', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    renderCsvFeedback(
                        data.message || 'CSV inválido.',
                        Array.isArray(data.errors) ? data.errors : [String(data.errors || 'Erro na validação do CSV.')]
                    );
                    toastr.error(data.message || 'O CSV não passou na validação.');
                    return;
                }

                csvValidationReady = true;
                importBtn.disabled = false;
                const summary = data.data
                    ? `Validação OK: ${data.data.valid_rows}/${data.data.total_rows} linhas válidas.`
                    : 'Validação concluída com sucesso.';
                renderCsvFeedback(summary, []);
                toastr.success('CSV validado com sucesso. Você já pode importar.');
            } catch (error) {
                renderCsvFeedback(`Erro de conexão: ${error.message}`, [error.message]);
                toastr.error(`Erro ao validar CSV: ${error.message}`);
            } finally {
                validateBtn.disabled = false;
                validateBtn.classList.remove('loading');
            }
        }

        async function importCsvFile() {
            const fileInput = document.getElementById('csv-file');
            const importBtn = document.getElementById('btn-import-csv');

            if (!csvValidationReady) {
                toastr.warning('Valide o CSV antes de importar.');
                return;
            }

            if (!fileInput.files || fileInput.files.length === 0) {
                toastr.warning('Selecione um arquivo CSV para importar.');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', fileInput.files[0]);

            importBtn.disabled = true;
            importBtn.classList.add('loading');

            try {
                const response = await fetch('<?php echo $basePath; ?>/api/musicas/import-csv', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    renderCsvFeedback(
                        data.message || 'Falha na importação.',
                        Array.isArray(data.errors) ? data.errors : [String(data.errors || 'Erro na importação do CSV.')]
                    );
                    toastr.error(data.message || 'Não foi possível importar o CSV.');
                    return;
                }

                const imported = data.data?.imported_rows ?? 0;
                renderCsvFeedback(`Importação concluída: ${imported} linhas importadas.`, []);
                toastr.success(data.message || 'CSV importado com sucesso.');
                await refreshSongsFromApi();

                fileInput.value = '';
                resetCsvValidationState();
            } catch (error) {
                renderCsvFeedback(`Erro de conexão: ${error.message}`, [error.message]);
                toastr.error(`Erro ao importar CSV: ${error.message}`);
            } finally {
                importBtn.classList.remove('loading');
            }
        }

        function editMusic(musicId) {
            const song = getSongById(musicId);
            if (!song) {
                toastr.error('Música não encontrada para edição.');
                return;
            }

            openEditModal(song);
        }

        async function deleteMusic(musicId) {
            if (!confirm('Tem certeza que deseja deletar esta música?')) {
                return;
            }

            try {
                const response = await fetch(`<?php echo $basePath; ?>/api/musicas/${musicId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    toastr.error(data.message || 'Erro ao deletar música.');
                    return;
                }

                state.songs = state.songs.filter(m => Number(m.id) !== Number(musicId));
                renderSongs();
                toastr.success('Música deletada com sucesso.');
            } catch (error) {
                toastr.error(`Erro de conexão: ${error.message}`);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderSongs();

            const editForm = document.getElementById('edit-form');
            const cancelBtn = document.getElementById('edit-cancel');
            const cancelTopBtn = document.getElementById('edit-cancel-top');
            const editModal = document.getElementById('edit-modal');
            const saveEditBtn = document.getElementById('edit-save');

            const closeIfBackdrop = (event) => {
                if (event.target === editModal) {
                    closeEditModal();
                }
            };

            cancelBtn.addEventListener('click', closeEditModal);
            cancelTopBtn.addEventListener('click', closeEditModal);
            editModal.addEventListener('click', closeIfBackdrop);

            editForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!currentEditingId) {
                    toastr.error('Nenhuma música selecionada para edição.');
                    return;
                }

                const { valid, payload } = buildEditPayload();
                if (!valid || !payload) {
                    toastr.warning('Preencha todos os campos da edição.');
                    return;
                }

                saveEditBtn.disabled = true;
                saveEditBtn.classList.add('loading');

                try {
                    const response = await fetch(`<?php echo $basePath; ?>/api/musicas/${currentEditingId}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        if (data.errors) {
                            Object.values(data.errors).forEach((msg) => toastr.error(String(msg)));
                        } else {
                            toastr.error(data.message || 'Erro ao atualizar música.');
                        }
                        return;
                    }

                    state.songs = state.songs.map((song) =>
                        Number(song.id) === Number(currentEditingId) ? data.data : song
                    );
                    renderSongs();
                    closeEditModal();
                    toastr.success('Música atualizada com sucesso.');
                } catch (error) {
                    toastr.error(`Erro de conexão: ${error.message}`);
                } finally {
                    saveEditBtn.disabled = false;
                    saveEditBtn.classList.remove('loading');
                }
            });

            const csvFileInput = document.getElementById('csv-file');
            const validateCsvBtn = document.getElementById('btn-validate-csv');
            const importCsvBtn = document.getElementById('btn-import-csv');

            csvFileInput.addEventListener('change', () => {
                resetCsvValidationState();
                document.getElementById('csv-feedback').classList.add('hidden');
            });
            validateCsvBtn.addEventListener('click', validateCsvFile);
            importCsvBtn.addEventListener('click', importCsvFile);
        });
    </script>
</body>
</html>

