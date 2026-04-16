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

// Získání statistik pro karty
$stats_lektori = $pdo->query("SELECT COUNT(*) FROM lektori")->fetchColumn();
$stats_kurzy = $pdo->query("SELECT COUNT(*) FROM kurzy")->fetchColumn();
$stats_studenti = $pdo->query("SELECT COUNT(*) FROM studenti")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduManager - Celková Evidence</title>
    <style>
        :root {
            --bg: #f4f4f4;
            --white: #fff;
            --container: 1100px;
            --radius: 12px;
            --grad: linear-gradient(90deg, rgba(132, 136, 245, 1) 0%, rgba(58, 55, 219, 1) 50%, rgba(25, 25, 158, 1) 100%, rgba(0, 12, 242, 1) 100%);
        }

        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding-top: 100px; }

        /* Navigace */
        .topnav { color: white; position: fixed; top: 0; left: 0; right: 0; background: var(--grad); height: 80px; display: flex; align-items: center; padding: 0 20px; z-index: 1000; }
        .logo { font-weight: bold; font-size: 22px; margin-right: auto; }
        .navigation a { padding: 10px 15px; color: white; text-decoration: none; font-weight: bold; }
        .navigation a:hover { background: rgba(255,255,255,0.2); border-radius: 6px; }

        .container { max-width: var(--container); margin: 0 auto; padding: 20px; }

        /* Statistiky */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 30px; border-radius: var(--radius); text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-bottom: 5px solid #3a37db; }
        .stat-card h2 { font-size: 48px; margin: 10px 0; color: #3a37db; }
        .stat-card p { color: #666; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }

        /* Tabulky */
        .evidence-block { background: white; padding: 25px; border-radius: var(--radius); box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        .search-box { padding: 10px 20px; border: 1px solid #ddd; border-radius: 25px; width: 300px; outline: none; }
        .search-box:focus { border-color: #3a37db; box-shadow: 0 0 8px rgba(58, 55, 219, 0.2); }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #f9f9fb; color: #333; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background-color: #fcfcff; }

        .badge { padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold; background: #eef2ff; color: #3a37db; }
    </style>
</head>
<body>

    <nav class="topnav">
        <div class="logo">EduManager | Evidence</div>
        <div class="navigation">
            <a href="index.php">← Zpět na správu</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="stats-grid">
            <div class="stat-card">
                <p>Celkem Lektorů</p>
                <h2><?php echo $stats_lektori; ?></h2>
            </div>
            <div class="stat-card">
                <p>Aktivních Kurzů</p>
                <h2><?php echo $stats_kurzy; ?></h2>
            </div>
            <div class="stat-card">
                <p>Zapsaných Studentů</p>
                <h2><?php echo $stats_studenti; ?></h2>
            </div>
        </div>

        <div class="evidence-block">
            <div class="header-row">
                <h2>Podrobný přehled kurzů</h2>
                <input type="text" id="searchCourse" class="search-box" placeholder="Hledat kurz nebo lektora..." onkeyup="filterTable('searchCourse', 'tableCourse')">
            </div>
            <table id="tableCourse">
                <thead>
                    <tr>
                        <th>Název kurzu</th>
                        <th>Lektor</th>
                        <th>Počet přihlášených</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT k.nazev, l.jmeno as lektor, 
                            (SELECT COUNT(*) FROM prihlasky p WHERE p.kurz_id = k.id) as pocet
                            FROM kurzy k
                            JOIN lektori l ON k.lektor_id = l.id
                            ORDER BY pocet DESC";
                    foreach ($pdo->query($sql) as $row) {
                        echo "<tr>
                                <td><strong>{$row['nazev']}</strong></td>
                                <td>{$row['lektor']}</td>
                                <td><span class='badge'>{$row['pocet']} účastníků</span></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="evidence-block">
            <div class="header-row">
                <h2>Seznam všech studentů</h2>
                <input type="text" id="searchStudent" class="search-box" placeholder="Hledat studenta..." onkeyup="filterTable('searchStudent', 'tableStudent')">
            </div>
            <table id="tableStudent">
                <thead>
                    <tr>
                        <th>Jméno studenta</th>
                        <th>Email</th>
                        <th>Počet kurzů</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT s.jmeno, s.email, 
                            (SELECT COUNT(*) FROM prihlasky p WHERE p.student_id = s.id) as pocet
                            FROM studenti s
                            ORDER BY s.jmeno ASC";
                    foreach ($pdo->query($sql) as $row) {
                        echo "<tr>
                                <td>{$row['jmeno']}</td>
                                <td>{$row['email']}</td>
                                <td><span class='badge'>{$row['pocet']} kurzů</span></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        // Funkce pro dynamické vyhledávání v tabulkách
        function filterTable(inputId, tableId) {
            let input = document.getElementById(inputId);
            let filter = input.value.toUpperCase();
            let table = document.getElementById(tableId);
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let text = tr[i].textContent || tr[i].innerText;
                tr[i].style.display = text.toUpperCase().indexOf(filter) > -1 ? "" : "none";
            }
        }
    </script>

</body>
</html>