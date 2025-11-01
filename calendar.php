<?php
// calendar.php
require_once 'includes/config.php';
require_once 'includes/helpers.php';

$events = $pdo->query("SELECT * FROM calendar_events ORDER BY date_debut")->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>
<h2>Calendrier scolaire</h2>
<div id="calendar-root"></div>
<script src="/itac/assets/js/calendar.js"></script>
<script>
  const events = <?= json_encode($events) ?>;
  initCalendar('calendar-root', events); // calendar.js doit dessiner et colorer selon event.couleur
</script>
<?php include 'includes/footer.php'; ?>
