<?php
declare(strict_types=1);

/**
 * Ritorna lista tavoli con stato calcolato (V2.0)
 *
 * @param PDO $pdo
 * @return array
 */
function v2_get_tavoli_con_stato(PDO $pdo): array
{
  $sql = "
    SELECT
      t.id,
      t.nome,
      t.posti,

      CASE
        WHEN EXISTS (
          SELECT 1
          FROM v2_prenotazioni p
          WHERE p.tavolo_id = t.id
            AND p.data = CURDATE()
            AND p.stato = 'arrivato'
        ) THEN 'occupato'

        WHEN EXISTS (
          SELECT 1
          FROM v2_prenotazioni p
          WHERE p.tavolo_id = t.id
            AND p.data = CURDATE()
            AND p.stato = 'prenotata'
        ) THEN 'prenotato'

        ELSE 'libero'
      END AS stato

    FROM v2_tavoli t
    WHERE t.attivo = 1
    ORDER BY t.id
  ";

  return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}