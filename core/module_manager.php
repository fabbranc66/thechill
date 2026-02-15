<?php
declare(strict_types=1);

/*
 |-----------------------------------------------------
 | Module Manager
 | Gestione installazione e attivazione moduli
 |-----------------------------------------------------
*/

class ModuleManager
{
    private PDO $pdo;
    private string $modulesPath;

    public function __construct(PDO $pdo, string $modulesPath)
    {
        $this->pdo = $pdo;
        $this->modulesPath = rtrim($modulesPath, '/');
    }

    /* =========================================
       Elenco moduli presenti su disco
    ========================================= */
    public function getAvailableModules(): array
    {
        $dirs = glob($this->modulesPath . '/*', GLOB_ONLYDIR);
        $modules = [];

        foreach ($dirs as $dir) {
            $json = $dir . '/module.json';
            if (file_exists($json)) {
                $data = json_decode(file_get_contents($json), true);
                $modules[$data['name']] = $data;
            }
        }

        return $modules;
    }

    /* =========================================
       Moduli installati (DB)
    ========================================= */
    public function getInstalledModules(): array
    {
        $stmt = $this->pdo->query("SELECT nome, attivo FROM moduli");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $out[$r['nome']] = (bool)$r['attivo'];
        }

        return $out;
    }

    /* =========================================
       Moduli attivi
    ========================================= */
    public function getActiveModules(): array
    {
        $stmt = $this->pdo->query("SELECT nome FROM moduli WHERE attivo = 1");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /* =========================================
       Installazione modulo
    ========================================= */
    public function install(string $moduleName): void
    {
        $moduleDir = $this->modulesPath . '/' . $moduleName;
        $infoFile = $moduleDir . '/module.json';

        if (!file_exists($infoFile)) {
            throw new Exception("Modulo non valido");
        }

        $info = json_decode(file_get_contents($infoFile), true);

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM moduli WHERE nome = ?"
        );
        $stmt->execute([$moduleName]);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Modulo già installato");
        }

        $this->pdo->beginTransaction();

        $installFile = $moduleDir . '/install.php';
        if (file_exists($installFile)) {
            require $installFile;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO moduli (nome, versione, attivo)
             VALUES (?, ?, 1)"
        );
        $stmt->execute([
            $info['name'],
            $info['version']
        ]);

        $this->pdo->commit();
    }

    /* =========================================
       Disinstallazione modulo
    ========================================= */
    public function uninstall(string $moduleName): void
    {
        $moduleDir = $this->modulesPath . '/' . $moduleName;

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM moduli WHERE nome = ?"
        );
        $stmt->execute([$moduleName]);

        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Modulo non installato");
        }

        $this->pdo->beginTransaction();

        $uninstallFile = $moduleDir . '/uninstall.php';
        if (file_exists($uninstallFile)) {
            require $uninstallFile;
        }

        $stmt = $this->pdo->prepare(
            "DELETE FROM moduli WHERE nome = ?"
        );
        $stmt->execute([$moduleName]);

        $this->pdo->commit();
    }

    /* =========================================
       Attiva modulo
    ========================================= */
    public function activate(string $moduleName): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE moduli SET attivo = 1 WHERE nome = ?"
        );
        $stmt->execute([$moduleName]);
    }

    /* =========================================
       Disattiva modulo
    ========================================= */
    public function deactivate(string $moduleName): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE moduli SET attivo = 0 WHERE nome = ?"
        );
        $stmt->execute([$moduleName]);
    }
}
