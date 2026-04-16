<?php
// 1. Připojení k databázi
$host = 'localhost';
$db   = 'sprava_kurzu';
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Chyba připojení: " . $e->getMessage());
}

$zprava = "";

// 2. Logika zpracování
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['akce'])) {
    if ($_POST['akce'] == 'pridat_lektora') {
        $stmt = $pdo->prepare("INSERT INTO lektori (jmeno, specializace) VALUES (?, ?)");
        $stmt->execute([$_POST['jmeno_lektora'], $_POST['specializace']]);
        $zprava = "Lektor přidán!";
    } elseif ($_POST['akce'] == 'pridat_kurz') {
        $stmt = $pdo->prepare("INSERT INTO kurzy (nazev, popis, lektor_id) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['nazev_kurzu'], $_POST['popis_kurzu'], $_POST['lektor_id']]);
        $zprava = "Kurz vytvořen!";
    } elseif ($_POST['akce'] == 'registrovat_studenta') {
        $stmt = $pdo->prepare("INSERT INTO studenti (jmeno, email) VALUES (?, ?)");
        $stmt->execute([$_POST['jmeno_studenta'], $_POST['email_studenta']]);
        $sid = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO prihlasky (student_id, kurz_id) VALUES (?, ?)");
        $stmt->execute([$sid, $_POST['kurz_id']]);
        $zprava = "Student přihlášen!";
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduManager - Správa kurzů</title>
    <style>
        :root {
            --bg: #f4f4f4;
            --dark: #111;
            --muted: #666;
            --accent: #0077b6;
            --white: #fff;
            --card-bg: rgba(0,0,0,0.6);
            --container: 1100px;
            --radius: 6px;
            --header-height: 500px; /* Upraveno pro lepší čitelnost */
            --grad: linear-gradient(90deg, rgba(132, 136, 245, 1) 0%, rgba(58, 55, 219, 1) 50%, rgba(25, 25, 158, 1) 100%, rgba(0, 12, 242, 1) 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--dark);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Topnav */
        .topnav {
            color: white;
            font-weight: bold;
            position: fixed;
            top: 0; left: 0; right: 0;
            background: var(--grad);
            height: 80px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .logo { display: flex; align-items: center; margin-right: auto; }
        .logo img { height: 44px; width: auto; }

        .navigation { display: flex; gap: 20px; }
        .navigation a { padding: 6px 12px; transition: all 0.3s ease; text-decoration: none; color: white; }
        .navigation a:hover { background: rgba(255, 255, 255, 0.2); border-radius: var(--radius); }

        /* Intro */
        .intro {
            text-align: center;
            padding: 60px 20px;
            background: url('https://media.istockphoto.com/id/1473121061/vector/vector-illustration-with-blue-curves-overlapping-background-margins-and-gradient-waves-and.jpg?s=612x612&w=0&k=20&c=Mp7wTkbVW2opnPEA-UzZvt8yjYMKo12Bkv7LHApDXqY=') no-repeat center center/cover;
            color: var(--white);
            height: var(--header-height);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-bottom: rgba(0, 58, 247, 0.5) 100px solid;
            margin-bottom: 30px;
            margin-top: 80px;
        }

        .intro-block {
            position: relative;
            background: var(--grad);
            opacity: 0.95;
            padding: 40px 80px;
            border-radius: 30px;
            transition: all 0.5s ease;
        }
        .intro-block:hover { transform: translateY(-5px); box-shadow: 0 10px 24px rgba(0,0,0,0.3); }

        /* Forms and Tables */
        .tables {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            max-width: var(--container);
            margin: -100px auto 60px; /* Překrytí intro sekce pro moderní vzhled */
            padding: 0 20px;
            justify-content: center;
            position: relative;
            z-index: 10;
        }

        .table-card {
            background: rgba(255,255,255,0.98);
            color: var(--dark);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            flex: 1 1 300px;
            max-width: 350px;
        }

        /* Styl pro inputy v kartách */
        .table-card input, .table-card select, .table-card textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: var(--radius);
            box-sizing: border-box;
        }

        .btn-submit {
            background: var(--grad);
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Přehledová tabulka pod kartami */
        .full-width-table {
            max-width: var(--container);
            margin: 40px auto;
            padding: 0 20px;
        }

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th { background: #3a37db; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; }

        .alert { 
            position: fixed; top: 100px; right: 20px; background: #2ecc71; color: white; 
            padding: 15px 25px; border-radius: var(--radius); z-index: 2000;
        }
    </style>
</head>
<body>

    <nav class="topnav">
        <div class="logo">
            <img src="https://cdn-icons-png.flaticon.com/512/3413/3413535.png" alt="Logo">
            <span style="margin-left: 10px; font-size: 24px;">EduManager</span>
        </div>
        <div class="navigation">
            <a href="#">Domů</a>
            <a href="#lektoři">Účastníci</a>
            <a href="evidence.php">Evidence</a>
            
        </div>
        <div class="search">
            <input type="text" placeholder="Hledat kurz...">
        </div>
        <button class="btn-logoff">Odhlásit</button>
    </nav>

    <header class="intro">
        <div class="intro-block">
            <h1>Správa vzdělávání</h1>
            <p id="p1">Kompletní evidence lektorů, kurzů a účastníků na jednom místě.</p>
        </div>
    </header>

    <?php if ($zprava): ?>
        <div class="alert"><?php echo $zprava; ?></div>
    <?php endif; ?>

    <section class="tables">
        
        <div class="table-card" id="lektoři">
            <h3>Nový Lektor</h3>
            <form method="post">
                <input type="hidden" name="akce" value="pridat_lektora">
                <input type="text" name="jmeno_lektora" placeholder="Jméno a příjmení" required>
                <input type="text" name="specializace" placeholder="Specializace (např. IT, Marketing)">
                <button type="submit" class="btn-submit">Uložit lektora</button>
            </form>
        </div>

        <div class="table-card" id="kurzy">
            <h3>Nový Kurz</h3>
            <form method="post">
                <input type="hidden" name="akce" value="pridat_kurz">
                <input type="text" name="nazev_kurzu" placeholder="Název kurzu" required>
                <select name="lektor_id" required>
                    <option value="">Vyberte lektora</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, jmeno FROM lektori");
                    while($l = $stmt->fetch()) echo "<option value='{$l['id']}'>{$l['jmeno']}</option>";
                    ?>
                </select>
                <button type="submit" class="btn-submit">Vytvořit kurz</button>
            </form>
        </div>

        <div class="table-card" id="studenti">
            <h3>Zápis Studenta</h3>
            <form method="post">
                <input type="hidden" name="akce" value="registrovat_studenta">
                <input type="text" name="jmeno_studenta" placeholder="Jméno studenta" required>
                <input type="email" name="email_studenta" placeholder="E-mailová adresa" required>
                <select name="kurz_id" required>
                    <option value="">Vyberte kurz</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, nazev FROM kurzy");
                    while($k = $stmt->fetch()) echo "<option value='{$k['id']}'>{$k['nazev']}</option>";
                    ?>
                </select>
                <button type="submit" class="btn-submit">Zapsat na kurz</button>
            </form>
        </div>

    </section>

    <section class="full-width-table">
        <h2>Aktuální přehled účastníků</h2>
        <table>
            <thead>
                <tr>
                    <th>Kurz</th>
                    <th>Lektor</th>
                    <th>Student</th>
                    <th>Datum přihlášení</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT k.nazev as kn, l.jmeno as ln, s.jmeno as sn, p.datum_prihlaseni 
                        FROM prihlasky p
                        JOIN kurzy k ON p.kurz_id = k.id
                        JOIN lektori l ON k.lektor_id = l.id
                        JOIN studenti s ON p.student_id = s.id
                        ORDER BY p.datum_prihlaseni DESC";
                foreach ($pdo->query($sql) as $row) {
                    echo "<tr>
                            <td><strong>{$row['kn']}</strong></td>
                            <td>{$row['ln']}</td>
                            <td>{$row['sn']}</td>
                            <td>" . date('d. m. Y', strtotime($row['datum_prihlaseni'])) . "</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <script>
        // Automatické schování hlášky po 3 sekundách
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if(alert) alert.style.display = 'none';
        }, 3000);
    </script>
</body>
</html>