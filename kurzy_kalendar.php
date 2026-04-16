<?php
// Připojení k databázi
$host = 'localhost';
$db   = 'sprava_kurzu';
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Chyba připojení: " . $e->getMessage());
}

// Získej měsíc a rok
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Zajistí, aby měsíc byl mezi 1-12
if ($current_month < 1) {
    $current_month = 12;
    $current_year--;
} elseif ($current_month > 12) {
    $current_month = 1;
    $current_year++;
}

// Název měsíce
$months = ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 
           'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'];
$month_name = $months[$current_month - 1];

// První den měsíce a počet dní
$first_day = mktime(0, 0, 0, $current_month, 1, $current_year);
$num_days = date('t', $first_day);
$day_of_week = date('N', $first_day);
$start_day = $day_of_week - 1;

// Navigace
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Zpráva
$zprava = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['akce'])) {
    if ($_POST['akce'] == 'pridat_kurz_datum') {
        // Přidání nového termínu
        $stmt = $pdo->prepare("INSERT INTO kurzy_terminy (kurz_id, datum) VALUES (?, ?)");
        $datum = $current_year . '-' . str_pad($current_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($_POST['den'], 2, '0', STR_PAD_LEFT);
        try {
            $stmt->execute([$_POST['kurz_id'], $datum]);
            $zprava = "✓ Termín přidán na " . $_POST['den'] . ". " . $month_name;
        } catch (Exception $e) {
            $zprava = "⚠ Tento termín již existuje";
        }
    } elseif ($_POST['akce'] == 'smazat_kurz_datum') {
        // Smazání termínu
        $stmt = $pdo->prepare("DELETE FROM kurzy_terminy WHERE id = ?");
        $stmt->execute([$_POST['termin_id']]);
        $zprava = "✓ Termín byl odstraněn z kalendáře";
    }
}

// Získej všechny dostupné kurzy (ne jen z tohoto měsíce)
$vsechny_kurzy_result = $pdo->query("SELECT k.*, l.jmeno as lektor_jmeno FROM kurzy k LEFT JOIN lektori l ON k.lektor_id = l.id ORDER BY k.nazev");
$vsechny_kurzy = $vsechny_kurzy_result->fetchAll(PDO::FETCH_ASSOC);

// Získej termíny kurzů pro tento měsíc
$terminy_result = $pdo->query("SELECT kt.*, k.nazev FROM kurzy_terminy kt LEFT JOIN kurzy k ON kt.kurz_id = k.id WHERE MONTH(kt.datum) = $current_month AND YEAR(kt.datum) = $current_year ORDER BY kt.datum");
$terminy = $terminy_result->fetchAll(PDO::FETCH_ASSOC);

// Vytvoř pole pro rychlý přístup k termínům podle dne
$terminy_by_day = [];
foreach ($terminy as $termin) {
    $den = (int)date('d', strtotime($termin['datum']));
    if (!isset($terminy_by_day[$den])) {
        $terminy_by_day[$den] = [];
    }
    $terminy_by_day[$den][] = $termin;
}

$today = date('d');
$today_month = date('m');
$today_year = date('Y');
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa kurzů - Kalendář</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            color: white;
            font-size: 36px;
        }

        .header-nav a {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .header-nav a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .calendar-wrapper {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .calendar-header h2 {
            flex: 1;
            text-align: center;
            color: #333;
            font-size: 28px;
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
        }

        .calendar-nav a {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .calendar-nav a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .calendar-year {
            font-size: 14px;
            color: #666;
            padding: 8px 16px;
            background: #f0f0f0;
            border-radius: 20px;
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }

        .day-header {
            text-align: center;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 12px 8px;
            border-radius: 6px;
            font-size: 14px;
        }

        .day {
            min-height: 100px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .day:hover {
            border-color: #667eea;
            background: #f5f5ff;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .day.other-month {
            background: #f0f0f0;
            opacity: 0.5;
            cursor: default;
        }

        .day.today {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .day-num {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .day.today .day-num {
            color: white;
        }

        .day-kurzy {
            font-size: 12px;
            line-height: 1.3;
        }

        .kurz-badge {
            background: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 3px;
            margin-top: 2px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .kurz-badge-text {
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .kurz-badge-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 2px;
            padding: 0 3px;
            cursor: pointer;
            font-size: 10px;
            font-weight: bold;
            flex-shrink: 0;
            transition: background 0.2s ease;
        }

        .kurz-badge-btn:hover {
            background: #ff5252;
        }

        .day.today .kurz-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .day.today .kurz-badge-btn {
            background: rgba(255, 255, 255, 0.3);
        }

        .day.today .kurz-badge-btn:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .right-panel {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            height: fit-content;
        }

        .right-panel h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 8px rgba(102, 126, 234, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .zprava {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .hidden-input {
            display: none;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            .calendar-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Správa kurzů</h1>
            <div class="header-nav">
                <a href="evidence.php">← Zpět na evidenci</a>
            </div>
        </div>

        <?php if ($zprava): ?>
            <div class="zprava"><?php echo htmlspecialchars($zprava); ?></div>
        <?php endif; ?>

        <div class="content">
            <div class="calendar-wrapper">
                <div class="calendar-header">
                    <h2><?php echo $month_name; ?></h2>
                    <div class="calendar-nav">
                        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>">←</a>
                        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>">→</a>
                    </div>
                    <div class="calendar-year"><?php echo $current_year; ?></div>
                </div>

                <div class="calendar">
                    <?php
                    // Záhlaví - dny v týdnu
                    $days_names = ['Po', 'Út', 'St', 'Čt', 'Pá', 'So', 'Ne'];
                    foreach ($days_names as $day_name) {
                        echo '<div class="day-header">' . $day_name . '</div>';
                    }

                    // Prázdná pole před prvním dnem
                    for ($i = 0; $i < $start_day; $i++) {
                        echo '<div class="day other-month"></div>';
                    }

                    // Dny měsíce
                    for ($day = 1; $day <= $num_days; $day++) {
                        $is_today = ($day == $today && $current_month == $today_month && $current_year == $today_year);
                        $class = $is_today ? 'day today' : 'day';
                        
                        echo '<div class="' . $class . '" onclick="selectDay(' . $day . ')">';
                        echo '<div class="day-num">' . $day . '</div>';
                        
                        // Zobraz termíny na daný den
                        if (isset($terminy_by_day[$day])) {
                            echo '<div class="day-kurzy">';
                            foreach ($terminy_by_day[$day] as $termin) {
                                echo '<span class="kurz-badge">';
                                echo '<span class="kurz-badge-text">' . htmlspecialchars(substr($termin['nazev'], 0, 12)) . '</span>';
                                echo '<form method="POST" style="display: inline; margin: 0;" onsubmit="return confirm(\'Opravdu odstranit tento termín?\');">';
                                echo '<input type="hidden" name="akce" value="smazat_kurz_datum">';
                                echo '<input type="hidden" name="termin_id" value="' . $termin['id'] . '">';
                                echo '<button type="submit" class="kurz-badge-btn" title="Odstranit">×</button>';
                                echo '</form>';
                                echo '</span>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }

                    // Prázdná pole po posledním dni
                    $total_cells = $start_day + $num_days;
                    $remaining = (7 - ($total_cells % 7)) % 7;
                    for ($i = 0; $i < $remaining; $i++) {
                        echo '<div class="day other-month"></div>';
                    }
                    ?>
                </div>
            </div>

            <div class="right-panel">
                <h3>Přiřadit kurz</h3>
                <form method="POST">
                    <input type="hidden" name="akce" value="pridat_kurz_datum">
                    <input type="hidden" name="den" id="selected_den" value="<?php echo date('d'); ?>">
                    
                    <div class="form-group">
                        <label>Vybraný den: <strong id="selected_day_display"><?php echo date('d'); ?>. <?php echo $month_name; ?> <?php echo $current_year; ?></strong></label>
                    </div>

                    <div class="form-group">
                        <label for="kurz">Vyberte kurz *</label>
                        <select id="kurz" name="kurz_id" required onchange="updateKurzInfo()">
                            <option value="">-- Vyberte kurz --</option>
                            <?php foreach ($vsechny_kurzy as $kurz): ?>
                                <option value="<?php echo $kurz['id']; ?>" data-lektor="<?php echo htmlspecialchars($kurz['lektor_jmeno'] ?? 'Bez lektora'); ?>">
                                    <?php echo htmlspecialchars($kurz['nazev']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="kurz-info" style="display: none;">
                        <label>Detaily kurzu:</label>
                        <div style="background: #f0f0f0; padding: 12px; border-radius: 6px; font-size: 13px;">
                            <p style="margin: 0;"><strong>Lektor:</strong> <span id="info-lektor">-</span></p>
                            <p style="margin: 5px 0 0;"><strong>Kapacita:</strong> <span id="info-kapacita">-</span></p>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Přiřadit kurz</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectDay(day) {
            document.getElementById('selected_den').value = day;
            const months = ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 
                           'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'];
            const currentMonth = <?php echo $current_month; ?>;
            const currentYear = <?php echo $current_year; ?>;
            document.getElementById('selected_day_display').textContent = day + '. ' + months[currentMonth - 1] + ' ' + currentYear;
        }

        function updateKurzInfo() {
            const select = document.getElementById('kurz');
            const option = select.options[select.selectedIndex];
            const infoDiv = document.getElementById('kurz-info');
            
            if (select.value) {
                const lektor = option.getAttribute('data-lektor');
                document.getElementById('info-lektor').textContent = lektor;
                document.getElementById('info-kapacita').textContent = '30 studentů';
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        }
    </script>
</body>
</html>
