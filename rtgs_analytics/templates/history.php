<?php
// templates/history.php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id']))
    die("Accès refusé.");
require_once __DIR__ . '/header.php';
?>

<div class="max-w-[1200px] mx-auto p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2 h-8 bg-purple-600 rounded-full block"></span>
                Historique des Sessions
            </h1>
            <p class="text-slate-500 text-sm mt-1 ml-4">Consultez les analyses précédentes et leur état.</p>
        </div>
        <div>
            <a href="index.php?action=etl"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition shadow-sm flex items-center gap-2">
                <span>⚡</span> Nouvelle Analyse
            </a>
        </div>
    </div>

    <!-- SESSIONS LIST -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    <th class="pb-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Nom de Session
                    </th>
                    <th class="pb-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">ID</th>
                    <th class="pb-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                    <th class="pb-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Utilisateur
                    </th>
                    <th class="pb-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="text-slate-600 text-sm">
                <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 italic">Aucune session d'analyse trouvée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sessions as $s): ?>
                        <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                            <td class="py-3 font-medium text-slate-800">
                                <?= !empty($s['name']) ? htmlspecialchars($s['name']) : 'Session #' . $s['id'] ?>
                            </td>
                            <td class="py-3 font-mono text-xs text-slate-500">#<?= $s['id'] ?></td>
                            <td class="py-3">
                                <span class="block text-slate-700"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></span>
                                <span class="text-xs text-slate-400"><?= time_elapsed_string($s['created_at']) ?></span>
                            </td>
                            <td class="py-3">
                                <span class="inline-flex items-center gap-2 px-2 py-1 bg-slate-100 rounded text-xs font-medium">
                                    👤 <?= htmlspecialchars($s['username']) ?>
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="index.php?action=dashboard&session_id=<?= $s['id'] ?>"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-100 transition">
                                    <span>📂</span> Ouvrir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Simple helper for time elapsed
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks and adjust days
    $weeks = floor($diff->d / 7);
    $diff->d -= $weeks * 7;

    $string = array(
        'y' => 'an',
        'm' => 'mois',
        'w' => 'semaine',
        'd' => 'jour',
        'h' => 'heure',
        'i' => 'minute',
        's' => 'seconde',
    );

    // Use an array to store values to avoid dynamic properties on DateInterval
    $diff_values = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks, // Use the calculated weeks
        'd' => $diff->d,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];

    foreach ($string as $k => &$v) {
        $value = $diff_values[$k];
        if ($value) {
            $v = $value . ' ' . $v . ($value > 1 && $k != 'm' ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? 'il y a ' . implode(', ', $string) : 'à l\'instant';
}
?>

<?php require_once __DIR__ . '/footer.php'; ?>