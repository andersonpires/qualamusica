<?php
/**
 * Classe MusicService - Business Logic Layer
 * 
 * Responsável por:
 * - Orquestrar operações de negócio
 * - Validações complexas
 * - Transformações de dados
 * - Integração com repositórios
 */

class MusicService
{
    private const CSV_HEADERS = ['nome', 'cantor', 'autor', 'link_karaoke', 'link_clipe', 'ano'];

    /**
     * Obtém todas as músicas
     */
    public static function getAllMusics(): array
    {
        return MusicRepository::findAll();
    }

    /**
     * Obtém uma música por ID
     */
    public static function getMusicById(int $id): ?Music
    {
        if ($id <= 0) {
            throw new Exception('ID deve ser maior que 0');
        }

        return MusicRepository::findById($id);
    }

    /**
     * Cria uma nova música (com validações)
     * 
     * @param array $data Array com dados da música
     * @return array Array com [success, message, data, errors]
     */
    public static function createMusic(array $data): array
    {
        try {
            // Valida YouTube links
            $youtubeValidation = self::validateYouTubeLinks(
                $data['link_karaoke'] ?? '',
                $data['link_clipe'] ?? ''
            );

            if (!$youtubeValidation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Links do YouTube inválidos',
                    'errors' => $youtubeValidation['errors'],
                ];
            }

            // Cria instância da música
            $music = new Music(
                nome: trim($data['nome'] ?? ''),
                cantor: trim($data['cantor'] ?? ''),
                autor: trim($data['autor'] ?? ''),
                link_karaoke: trim($data['link_karaoke'] ?? ''),
                link_clipe: trim($data['link_clipe'] ?? ''),
                ano: (int)($data['ano'] ?? 0)
            );

            // Valida campos obrigatórios
            $validationErrors = $music->validate();

            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Validação falhou',
                    'errors' => $validationErrors,
                ];
            }

            // Insere no banco
            $id = MusicRepository::insert($music);
            $music->id = $id;

            return [
                'success' => true,
                'message' => 'Música cadastrada com sucesso',
                'data' => $music->toArray(),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao cadastrar música',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ];
        }
    }

    /**
     * Atualiza uma música existente
     * 
     * @param int $id ID da música
     * @param array $data Array com dados para atualizar
     * @return array Array com [success, message, data, errors]
     */
    public static function updateMusic(int $id, array $data): array
    {
        try {
            // Obtém música existente
            $music = MusicRepository::findById($id);

            if (!$music) {
                return [
                    'success' => false,
                    'message' => 'Música não encontrada',
                ];
            }

            // Atualiza dados
            if (isset($data['nome'])) $music->nome = trim($data['nome']);
            if (isset($data['cantor'])) $music->cantor = trim($data['cantor']);
            if (isset($data['autor'])) $music->autor = trim($data['autor']);
            if (isset($data['link_karaoke'])) $music->link_karaoke = trim($data['link_karaoke']);
            if (isset($data['link_clipe'])) $music->link_clipe = trim($data['link_clipe']);
            if (isset($data['ano'])) $music->ano = (int)$data['ano'];

            // Valida YouTube links se forem atualizados
            $youtubeValidation = self::validateYouTubeLinks(
                $music->link_karaoke,
                $music->link_clipe
            );

            if (!$youtubeValidation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Links do YouTube inválidos',
                    'errors' => $youtubeValidation['errors'],
                ];
            }

            // Valida campos obrigatórios
            $validationErrors = $music->validate();

            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Validação falhou',
                    'errors' => $validationErrors,
                ];
            }

            // Atualiza no banco
            MusicRepository::update($music);

            return [
                'success' => true,
                'message' => 'Música atualizada com sucesso',
                'data' => $music->toArray(),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao atualizar música',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ];
        }
    }

    /**
     * Deleta uma música
     */
    public static function deleteMusic(int $id): array
    {
        try {
            $music = MusicRepository::findById($id);

            if (!$music) {
                return [
                    'success' => false,
                    'message' => 'Música não encontrada',
                ];
            }

            MusicRepository::delete($id);

            return [
                'success' => true,
                'message' => 'Música deletada com sucesso',
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao deletar música',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ];
        }
    }

    /**
     * Valida links do YouTube
     * 
     * Valida formatos:
     * - https://www.youtube.com/watch?v=VIDEO_ID
     * - https://youtu.be/VIDEO_ID
     * - https://youtube.com/watch?v=VIDEO_ID
     * - youtu.be/VIDEO_ID
     * - youtube.com/watch?v=VIDEO_ID
     */
    private static function validateYouTubeLinks(string $link1, string $link2): array
    {
        $errors = [];

        if (!self::isValidYouTubeUrl($link1)) {
            $errors['link_karaoke'] = 'Link do karaokê não é um URL válido do YouTube';
        }

        if (!self::isValidYouTubeUrl($link2)) {
            $errors['link_clipe'] = 'Link do clipe não é um URL válido do YouTube';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Verifica se uma URL é válida do YouTube
     */
    private static function isValidYouTubeUrl(string $url): bool
    {
        // Remove espaços
        $url = trim($url);

        if (empty($url)) {
            return false;
        }

        // Patterns possíveis do YouTube
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extrai ID do vídeo da URL do YouTube
     */
    public static function extractYouTubeVideoId(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Valida um CSV sem persistir dados
     *
     * @param array $uploadedFile Entrada de $_FILES['csv_file']
     * @return array
     */
    public static function validateCsvUpload(array $uploadedFile): array
    {
        try {
            $parsed = self::parseCsvUpload($uploadedFile);
            if (!$parsed['success']) {
                return $parsed;
            }

            $rows = $parsed['rows'];
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                $validation = self::validateCsvRow($row);
                if (!$validation['valid']) {
                    $errors[] = [
                        'line' => $rowIndex + 2,
                        'errors' => $validation['errors'],
                    ];
                }
            }

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'O CSV possui erros de validação.',
                    'data' => [
                        'total_rows' => count($rows),
                        'valid_rows' => count($rows) - count($errors),
                        'invalid_rows' => count($errors),
                    ],
                    'errors' => $errors,
                ];
            }

            return [
                'success' => true,
                'message' => 'CSV validado com sucesso. Pronto para importação.',
                'data' => [
                    'total_rows' => count($rows),
                    'valid_rows' => count($rows),
                    'invalid_rows' => 0,
                ],
                'errors' => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao validar CSV.',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ];
        }
    }

    /**
     * Importa dados de CSV para o banco (transacional)
     *
     * @param array $uploadedFile Entrada de $_FILES['csv_file']
     * @return array
     */
    public static function importCsvUpload(array $uploadedFile): array
    {
        try {
            $parsed = self::parseCsvUpload($uploadedFile);
            if (!$parsed['success']) {
                return $parsed;
            }

            $rows = $parsed['rows'];
            $errors = [];
            $musicsToInsert = [];

            foreach ($rows as $rowIndex => $row) {
                $validation = self::validateCsvRow($row);
                if (!$validation['valid']) {
                    $errors[] = [
                        'line' => $rowIndex + 2,
                        'errors' => $validation['errors'],
                    ];
                    continue;
                }

                $musicsToInsert[] = $validation['music'];
            }

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Importação cancelada: o CSV contém linhas inválidas.',
                    'data' => [
                        'total_rows' => count($rows),
                        'valid_rows' => count($musicsToInsert),
                        'invalid_rows' => count($errors),
                        'imported_rows' => 0,
                    ],
                    'errors' => $errors,
                ];
            }

            if (empty($musicsToInsert)) {
                return [
                    'success' => false,
                    'message' => 'Nenhuma linha válida para importar.',
                    'errors' => ['O arquivo CSV não possui dados válidos.'],
                ];
            }

            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            foreach ($musicsToInsert as $music) {
                MusicRepository::insert($music);
            }

            $pdo->commit();

            return [
                'success' => true,
                'message' => 'Importação concluída com sucesso.',
                'data' => [
                    'total_rows' => count($rows),
                    'imported_rows' => count($musicsToInsert),
                ],
                'errors' => null,
            ];
        } catch (Exception $e) {
            $pdo = Database::getConnection();
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Erro ao importar CSV.',
                'errors' => [Config::getBool('APP_DEBUG', false) ? $e->getMessage() : 'Erro interno'],
            ];
        }
    }

    /**
     * Faz parsing de upload CSV e retorna linhas associativas
     */
    private static function parseCsvUpload(array $uploadedFile): array
    {
        if (empty($uploadedFile) || !isset($uploadedFile['tmp_name'])) {
            return [
                'success' => false,
                'message' => 'Arquivo CSV não enviado.',
                'errors' => ['Selecione um arquivo CSV para continuar.'],
            ];
        }

        if (($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Falha no upload do CSV.',
                'errors' => ['Não foi possível receber o arquivo enviado.'],
            ];
        }

        $originalName = (string)($uploadedFile['name'] ?? '');
        if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'csv') {
            return [
                'success' => false,
                'message' => 'Formato inválido.',
                'errors' => ['Use um arquivo com extensão .csv.'],
            ];
        }

        $filePath = (string)$uploadedFile['tmp_name'];
        $delimiter = self::detectCsvDelimiter($filePath);

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return [
                'success' => false,
                'message' => 'Falha ao ler CSV.',
                'errors' => ['Não foi possível abrir o arquivo para leitura.'],
            ];
        }

        try {
            $headers = fgetcsv($handle, 0, $delimiter);
            if ($headers === false) {
                return [
                    'success' => false,
                    'message' => 'CSV vazio.',
                    'errors' => ['O arquivo CSV está vazio.'],
                ];
            }

            $headers = self::normalizeCsvHeaders($headers);
            if ($headers !== self::CSV_HEADERS) {
                return [
                    'success' => false,
                    'message' => 'Cabeçalho do CSV inválido.',
                    'errors' => [[
                        'expected' => self::CSV_HEADERS,
                        'received' => $headers,
                    ]],
                ];
            }

            $rows = [];
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (self::isCsvRowEmpty($data)) {
                    continue;
                }

                $data = array_pad($data, count(self::CSV_HEADERS), '');
                $row = array_combine(self::CSV_HEADERS, array_map('trim', $data));
                $rows[] = $row;
            }

            if (empty($rows)) {
                return [
                    'success' => false,
                    'message' => 'CSV sem linhas de dados.',
                    'errors' => ['Inclua pelo menos uma linha com dados para importar.'],
                ];
            }

            return [
                'success' => true,
                'message' => 'CSV lido com sucesso.',
                'rows' => $rows,
            ];
        } finally {
            fclose($handle);
        }
    }

    /**
     * Valida uma linha do CSV e retorna entidade pronta para persistência
     */
    private static function validateCsvRow(array $row): array
    {
        $music = new Music(
            nome: trim((string)($row['nome'] ?? '')),
            cantor: trim((string)($row['cantor'] ?? '')),
            autor: trim((string)($row['autor'] ?? '')),
            link_karaoke: trim((string)($row['link_karaoke'] ?? '')),
            link_clipe: trim((string)($row['link_clipe'] ?? '')),
            ano: (int)($row['ano'] ?? 0)
        );

        $errors = $music->validate();

        $youtubeValidation = self::validateYouTubeLinks($music->link_karaoke, $music->link_clipe);
        if (!$youtubeValidation['valid']) {
            foreach ($youtubeValidation['errors'] as $field => $message) {
                $errors[$field] = $message;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'music' => $music,
        ];
    }

    /**
     * Detecta delimitador predominante entre vírgula e ponto-e-vírgula
     */
    private static function detectCsvDelimiter(string $filePath): string
    {
        $line = '';
        $handle = fopen($filePath, 'rb');
        if ($handle !== false) {
            $line = (string)fgets($handle);
            fclose($handle);
        }

        $commaCount = substr_count($line, ',');
        $semicolonCount = substr_count($line, ';');

        return $semicolonCount > $commaCount ? ';' : ',';
    }

    /**
     * Normaliza cabeçalhos CSV removendo BOM e espaços
     */
    private static function normalizeCsvHeaders(array $headers): array
    {
        $normalized = array_map(static function ($header) {
            $header = (string)$header;
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
            return strtolower(trim($header));
        }, $headers);

        return array_values($normalized);
    }

    /**
     * Verifica se linha CSV é vazia
     */
    private static function isCsvRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string)$value) !== '') {
                return false;
            }
        }

        return true;
    }
}

