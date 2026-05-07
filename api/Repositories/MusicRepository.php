<?php
/**
 * Classe MusicRepository - Data Access Layer
 * 
 * Responsável por:
 * - CRUD no banco de dados
 * - Queries de acesso a dados
 * - Usar PDO com prepared statements (segurança)
 */

class MusicRepository
{
    /**
     * Obtém todas as músicas
     * 
     * @return array Array de instâncias Music
     */
    public static function findAll(): array
    {
        $sql = "SELECT * FROM musicas ORDER BY criado_em DESC";
        $rows = Database::fetchAll($sql);

        return array_map(fn($row) => self::rowToMusic($row), $rows);
    }

    /**
     * Obtém música por ID
     * 
     * @param int $id ID da música
     * @return Music|null Instância Music ou null se não encontrada
     */
    public static function findById(int $id): ?Music
    {
        $sql = "SELECT * FROM musicas WHERE id = ?";
        $row = Database::fetch($sql, [$id]);

        return $row ? self::rowToMusic($row) : null;
    }

    /**
     * Insere uma nova música no banco
     * 
     * @param Music $music Instância da música
     * @return int ID da música inserida
     * @throws Exception Se houver erro
     */
    public static function insert(Music $music): int
    {
        $sql = "INSERT INTO musicas (nome, cantor, autor, link_karaoke, link_clipe, ano) 
                VALUES (?, ?, ?, ?, ?, ?)";

        Database::query($sql, [
            $music->nome,
            $music->cantor,
            $music->autor,
            $music->link_karaoke,
            $music->link_clipe,
            $music->ano,
        ]);

        return (int)Database::lastInsertId();
    }

    /**
     * Atualiza uma música existente
     * 
     * @param Music $music Instância com dados a atualizar
     * @return bool True se atualizado com sucesso
     * @throws Exception Se houver erro
     */
    public static function update(Music $music): bool
    {
        if (!$music->id) {
            throw new Exception('ID da música é obrigatório para atualizar');
        }

        $sql = "UPDATE musicas 
                SET nome = ?, cantor = ?, autor = ?, 
                    link_karaoke = ?, link_clipe = ?, ano = ?
                WHERE id = ?";

        Database::query($sql, [
            $music->nome,
            $music->cantor,
            $music->autor,
            $music->link_karaoke,
            $music->link_clipe,
            $music->ano,
            $music->id,
        ]);

        return true;
    }

    /**
     * Deleta uma música
     * 
     * @param int $id ID da música
     * @return bool True se deletado com sucesso
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM musicas WHERE id = ?";
        Database::query($sql, [$id]);
        return true;
    }

    /**
     * Converte row de banco para instância Music
     * 
     * @param array $row Linha do banco de dados
     * @return Music Instância Music populada
     */
    private static function rowToMusic(array $row): Music
    {
        $music = new Music(
            nome: $row['nome'],
            cantor: $row['cantor'],
            autor: $row['autor'],
            link_karaoke: $row['link_karaoke'],
            link_clipe: $row['link_clipe'],
            ano: (int)$row['ano'],
            id: (int)$row['id']
        );

        $music->criado_em = $row['criado_em'] ?? null;
        $music->atualizado_em = $row['atualizado_em'] ?? null;

        return $music;
    }

    /**
     * Conta total de músicas cadastradas
     */
    public static function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM musicas";
        $row = Database::fetch($sql);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Busca músicas por texto (nome, cantor, autor)
     */
    public static function search(string $term): array
    {
        $term = "%{$term}%";
        $sql = "SELECT * FROM musicas 
                WHERE nome LIKE ? OR cantor LIKE ? OR autor LIKE ?
                ORDER BY nome ASC";

        $rows = Database::fetchAll($sql, [$term, $term, $term]);
        return array_map(fn($row) => self::rowToMusic($row), $rows);
    }
}
