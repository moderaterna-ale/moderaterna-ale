<?php
/**
 * Ale Quiz Backend API (PHP / MariaDB / SQLite Hybrid)
 * Moderaterna i Ale (ale.nu)
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Konfiguration
$dbHost = 'localhost';
$dbName = 'alenu_db';
$dbUser = 'alenu_erik';
$dbPass = 'PsYgF7PWdFjzhSy';
$adminPassword = 'Ale-M2026!SuperK0mmun#982';
$secretKey = 'ale_moderaterna_quiz_supersecret_key_2026';

// 8 Verifierade Frågor
$questions = [
    [
        'id' => 1,
        'category' => 'Allmän',
        'question' => 'Alependeln är en av Västsveriges snabbaste pendellinjer. Hur många minuter tar det ungefär med tåget från Göteborgs Central till Älvängen?',
        'options' => [
            ['id' => 'opt_1', 'text' => '27 minuter', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => '14 minuter', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => '58 minuter (om inte en ko står på spåret)', 'isCorrect' => false]
        ],
        'explanation' => 'Med Alependeln tar det bara ca 27 minuter från Göteborg C till Älvängen – snabbt, smidigt och klimatsmart i vardagen!'
    ],
    [
        'id' => 2,
        'category' => 'Allmän',
        'question' => 'Vilket unikt föremål hittades i Ale 1933?',
        'options' => [
            ['id' => 'opt_1', 'text' => 'Vikingaskepp', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => 'UFO', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => 'Kista med guld', 'isCorrect' => false]
        ],
        'explanation' => 'Äskekärrskeppet, Sveriges enda bevarade vikingaskepp (knarr), grävdes fram i Äskekärr i Ale 1933!'
    ],
    [
        'id' => 3,
        'category' => 'Allmän',
        'question' => 'Vilket vidsträckt och mytomspunnet vildmarksområde med hundratals sjöar och fina vandringsleder breder ut sig i östra delen av Ale kommun?',
        'options' => [
            ['id' => 'opt_1', 'text' => 'Risveden', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => 'Svartedalen', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => 'Sandsjöbacka', 'isCorrect' => false]
        ],
        'explanation' => 'Risveden är ett av Västsveriges största sammanhängande vildmarksområden och bjuder på fantastiska naturupplevelser, bad och leder för alla Alebor!'
    ],
    [
        'id' => 4,
        'category' => 'Profil',
        'question' => 'Psykisk ohälsa är ett stort problem, Moderaternas lösning är?',
        'options' => [
            ['id' => 'opt_1', 'text' => 'Rätt stöd när du behöver det', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => 'Fler blanketter att fylla i', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => 'Saft och bulle', 'isCorrect' => false]
        ],
        'explanation' => 'Moderaterna vill att stöd och insatser ska ges tidigt, utan krångel och med fokus på individens behov när man behöver det.'
    ],
    [
        'id' => 5,
        'category' => 'Profil',
        'question' => 'Moderaterna i Ale har en tydlig och ambitiös målsättning för kommunens utveckling. Vad är målet senast år 2030?',
        'options' => [
            ['id' => 'opt_1', 'text' => 'Bli nominerade till ”Årets Superkommun” med service och ekonomi i toppklass', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => 'Bygga Sveriges högsta skyskrapa i Skepplanda', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => 'Byta ut pendeltågen mot linbana', 'isCorrect' => false]
        ],
        'explanation' => 'Med en stabil ekonomi, sänkt skatt och fokus på välfärdens kärna vill Moderaterna göra Ale till en certifierad Superkommun senast 2030!'
    ],
    [
        'id' => 6,
        'category' => 'Profil',
        'question' => 'Vad tycker Moderaterna om en bilväg mellan Lövgärdet och Surte?',
        'options' => [
            ['id' => 'opt_1', 'text' => 'Vi säger bestämt NEJ – vi vill bevara Surtes unika karaktär, lugn och trygghet!', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => 'Vi tycker det låter som ett fantastiskt rallyspår', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => 'Vi vill bygga en åttafilad motorväg genom badsjön', 'isCorrect' => false]
        ],
        'explanation' => 'Moderaterna sätter Surtebornas trygghet, miljö och lokala karaktär först. Ingen bilväg till Lövgärdet!'
    ],
    [
        'id' => 7,
        'category' => 'Profil',
        'question' => 'Hur vill Moderaterna utveckla skolan i Ale?',
        'options' => [
            ['id' => 'opt_1', 'text' => 'Skolfrukost, kött varje dag, fler vuxna', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => 'Inga lov', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => '100 elever i varje klass', 'isCorrect' => false]
        ],
        'explanation' => 'Genom satsningar på skolfrukost, bra näringsrik mat med valmöjligheter och fler vuxna i skolan skapar vi trygghet och studiero så att varje elev kan nå sin fulla potential!'
    ],
    [
        'id' => 8,
        'category' => 'Profil',
        'question' => 'Ale har något som många kommuner avundas – milslång älvkontakt. Vad vill Moderaterna göra med området längs Göta älv?',
        'options' => [
            ['id' => 'opt_1', 'text' => 'Skapa liv, rörelse och aktivitet', 'isCorrect' => true],
            ['id' => 'opt_2', 'text' => 'Bygga en fem meter hög betongmur', 'isCorrect' => false],
            ['id' => 'opt_3', 'text' => 'Förbjuda all form av vistelse vid vattnet', 'isCorrect' => false]
        ],
        'explanation' => 'Moderaterna vill öppna upp älvstråket med promenadstråk, bryggor och mötesplatser så att alla Alebor kan njuta av närheten till vattnet!'
    ]
];

// Database connection with MariaDB + SQLite fallback and auto-sync
$GLOBALS['db_connected_driver'] = 'none';
$GLOBALS['db_connection_error'] = '';

function getDB() {
    global $dbHost, $dbName, $dbUser, $dbPass;
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $sqlitePath = __DIR__ . '/data/quiz.db';

    // List of MySQL DSNs to try (localhost socket, 127.0.0.1 TCP, standard ports)
    $dsns = [
        "mysql:host=localhost;dbname={$dbName};charset=utf8mb4",
        "mysql:host=127.0.0.1;port=3306;dbname={$dbName};charset=utf8mb4",
        "mysql:host=localhost;port=3306;dbname={$dbName};charset=utf8mb4",
        "mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname={$dbName};charset=utf8mb4",
        "mysql:unix_socket=/tmp/mysql.sock;dbname={$dbName};charset=utf8mb4"
    ];

    foreach ($dsns as $dsn) {
        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            $GLOBALS['db_connected_driver'] = 'mysql (' . $dsn . ')';
            break;
        } catch (Exception $e) {
            $GLOBALS['db_connection_error'] .= $dsn . ': ' . $e->getMessage() . ' | ';
            $pdo = null;
        }
    }

    if ($pdo !== null) {

        // Create table in MySQL
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS quiz_submissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                source VARCHAR(50) DEFAULT 'kexchoklad',
                name VARCHAR(150) NOT NULL,
                city VARCHAR(100) DEFAULT '',
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(150) NOT NULL,
                score INT NOT NULL,
                total_questions INT NOT NULL DEFAULT 8,
                tiebreaker_guess INT NOT NULL,
                prize_choice VARCHAR(100) NOT NULL,
                want_info TINYINT(1) DEFAULT 0,
                want_member TINYINT(1) DEFAULT 0,
                answers_json TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                INDEX idx_created (created_at),
                INDEX idx_score (score),
                INDEX idx_want_member (want_member)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        try {
            $pdo->exec("ALTER TABLE quiz_submissions ADD COLUMN city VARCHAR(100) DEFAULT '' AFTER name;");
        } catch (Exception $e) {}

        // Auto-seed from material/ale-quiz-deltagare-2026-09-12.csv if table is empty or missing rows
        $csvPath = __DIR__ . '/material/ale-quiz-deltagare-2026-09-12.csv';
        if (file_exists($csvPath)) {
            try {
                $cntStmt = $pdo->query("SELECT COUNT(*) FROM quiz_submissions");
                $currCount = (int)$cntStmt->fetchColumn();
                if ($currCount < 200) {
                    if (($handle = fopen($csvPath, "r")) !== FALSE) {
                        $header = fgetcsv($handle, 2000, ";"); // Skip header
                        $ins = $pdo->prepare("
                            INSERT IGNORE INTO quiz_submissions 
                            (id, created_at, source, name, city, phone, email, score, total_questions, tiebreaker_guess, prize_choice, want_info, want_member)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        while (($data = fgetcsv($handle, 2000, ";")) !== FALSE) {
                            if (count($data) >= 13) {
                                $ins->execute([
                                    (int)$data[0],
                                    $data[1],
                                    $data[2],
                                    $data[3],
                                    $data[4],
                                    $data[5],
                                    $data[6],
                                    (int)$data[7],
                                    (int)$data[8],
                                    (int)$data[9],
                                    $data[10],
                                    (mb_strtolower(trim($data[11])) === 'ja' ? 1 : 0),
                                    (mb_strtolower(trim($data[12])) === 'ja' ? 1 : 0)
                                ]);
                            }
                        }
                        fclose($handle);
                    }
                }
            } catch (Exception $csvErr) {}
        }

        // Auto-sync SQLite rows to MySQL if SQLite file exists
        if (file_exists($sqlitePath)) {
            try {
                $sqPdo = new PDO("sqlite:{$sqlitePath}");
                $sqRows = $sqPdo->query("SELECT * FROM quiz_submissions")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($sqRows as $r) {
                    $chk = $pdo->prepare("SELECT id FROM quiz_submissions WHERE id = ? LIMIT 1");
                    $chk->execute([$r['id']]);
                    if (!$chk->fetch()) {
                        $ins = $pdo->prepare("
                            INSERT INTO quiz_submissions (id, created_at, source, name, city, phone, email, score, total_questions, tiebreaker_guess, prize_choice, want_info, want_member, answers_json)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $ins->execute([
                            $r['id'],
                            $r['created_at'] ?? date('Y-m-d H:i:s'),
                            $r['source'] ?? 'kexchoklad',
                            $r['name'] ?? '',
                            $r['city'] ?? '',
                            $r['phone'] ?? '',
                            $r['email'] ?? '',
                            $r['score'] ?? 0,
                            $r['total_questions'] ?? 8,
                            $r['tiebreaker_guess'] ?? 0,
                            $r['prize_choice'] ?? '',
                            $r['want_info'] ?? 0,
                            $r['want_member'] ?? 0,
                            $r['answers_json'] ?? '[]'
                        ]);
                    }
                }
            } catch (Exception $syncErr) {}
        }

        return $pdo;
    } catch (Exception $mySqlErr) {
        // Fallback to SQLite
        try {
            if (!is_dir(__DIR__ . '/data')) {
                @mkdir(__DIR__ . '/data', 0755, true);
            }
            $pdo = new PDO("sqlite:{$sqlitePath}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS quiz_submissions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    source TEXT DEFAULT 'kexchoklad',
                    name TEXT NOT NULL,
                    city TEXT DEFAULT '',
                    phone TEXT NOT NULL,
                    email TEXT NOT NULL,
                    score INTEGER NOT NULL,
                    total_questions INTEGER NOT NULL DEFAULT 8,
                    tiebreaker_guess INTEGER NOT NULL,
                    prize_choice TEXT NOT NULL,
                    want_info INTEGER DEFAULT 0,
                    want_member INTEGER DEFAULT 0,
                    answers_json TEXT,
                    ip_address TEXT,
                    user_agent TEXT
                );
            ");
            return $pdo;
        } catch (Exception $sqErr) {
            return null;
        }
    }
}

// Token-validering för admin (Stödjer Headers, Query Parameter och Cookies)
function checkAuth() {
    global $secretKey;
    $token = $_GET['token'] ?? $_POST['token'] ?? '';

    if (!$token) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!$authHeader && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        if (!$authHeader && function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $m)) {
            $token = trim($m[1]);
        }
    }

    if (!$token) {
        $token = $_COOKIE['quiz_admin_token'] ?? '';
    }

    if (!$token) return false;

    $parts = explode('.', $token);
    if (count($parts) !== 2) return false;
    $payload = $parts[0];
    $sig = $parts[1];

    if (hash_hmac('sha256', $payload, $secretKey) === $sig) {
        $data = json_decode(base64_decode($payload), true);
        if ($data && isset($data['exp']) && $data['exp'] > time() && ($data['role'] ?? '') === 'admin') {
            return true;
        }
    }
    return false;
}

// Parse Route
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = $_GET['route'] ?? '';
if (!$route) {
    if (strpos($uri, '/api/quiz/') !== false) {
        $route = substr($uri, strpos($uri, '/api/quiz/') + 10);
    } elseif (strpos($uri, '/api/') !== false) {
        $route = substr($uri, strpos($uri, '/api/') + 5);
    }
}
if (strpos($route, 'quiz/') === 0) {
    $route = substr($route, 5);
}
$route = trim($route, '/');
$method = $_SERVER['REQUEST_METHOD'];

// JSON Helper
function jsonOut($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. GET /api/quiz/questions
if (($route === 'questions' || $route === 'quiz/questions') && $method === 'GET') {
    $q1 = $questions[0];
    $rest = array_slice($questions, 1);
    shuffle($rest);
    $ordered = array_merge([$q1], $rest);

    $clientQuestions = [];
    foreach ($ordered as $q) {
        $opts = $q['options'];
        shuffle($opts);
        $clientOpts = [];
        foreach ($opts as $o) {
            $clientOpts[] = ['id' => $o['id'], 'text' => $o['text']];
        }
        $clientQuestions[] = [
            'id' => $q['id'],
            'category' => $q['category'],
            'question' => $q['question'],
            'options' => $clientOpts
        ];
    }

    jsonOut(['success' => true, 'questions' => $clientQuestions]);
}

// 2. POST /api/quiz/submit
if (($route === 'submit' || $route === 'quiz/submit') && $method === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $_POST;

    $source = $body['source'] ?? 'kexchoklad';
    $name = trim($body['name'] ?? '');
    $city = trim($body['city'] ?? '');
    $phone = trim($body['phone'] ?? '');
    $email = trim($body['email'] ?? '');
    $answers = $body['answers'] ?? [];
    $tiebreaker = intval($body['tiebreakerGuess'] ?? 0);
    $prizeChoice = $body['prizeChoice'] ?? '500 kr presentkort i Ale';
    $wantInfo = !empty($body['wantInfo']) ? 1 : 0;
    $wantMember = !empty($body['wantMember']) ? 1 : 0;

    if (!$name || !$city || !$phone || !$email) {
        jsonOut(['error' => 'Vänligen fyll i alla obligatoriska fält inklusive hemort.'], 400);
    }

    $score = 0;
    $detailedAnswers = [];

    foreach ($questions as $q) {
        $qId = $q['id'];
        $userOptId = $answers[$qId] ?? null;

        $correctOpt = null;
        $userOpt = null;
        foreach ($q['options'] as $o) {
            if ($o['isCorrect']) $correctOpt = $o;
            if ($userOptId && $o['id'] === $userOptId) $userOpt = $o;
        }

        $isCorrect = ($correctOpt && $userOpt && $correctOpt['id'] === $userOpt['id']);
        if ($isCorrect) $score++;

        $detailedAnswers[] = [
            'questionId' => $qId,
            'question' => $q['question'],
            'userOptionId' => $userOptId,
            'userAnswerText' => $userOpt ? $userOpt['text'] : 'Inget svar',
            'correctOptionId' => $correctOpt ? $correctOpt['id'] : null,
            'correctAnswerText' => $correctOpt ? $correctOpt['text'] : '',
            'isCorrect' => $isCorrect,
            'explanation' => $q['explanation']
        ];
    }

    $pdo = getDB();
    if ($pdo) {
        $stmt = $pdo->prepare("
            INSERT INTO quiz_submissions (
                source, name, city, phone, email, score, total_questions,
                tiebreaker_guess, prize_choice, want_info, want_member,
                answers_json, ip_address, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $source,
            $name,
            $city,
            $phone,
            $email,
            $score,
            8,
            $tiebreaker,
            $prizeChoice,
            $wantInfo,
            $wantMember,
            json_encode($detailedAnswers, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    $diplomaTitle = 'Bra kämpat!';
    if ($score === 8) $diplomaTitle = '🏆 Full pott! Ale-expert i toppklass!';
    elseif ($score >= 6) $diplomaTitle = '🌟 Mycket väl godkänt!';
    elseif ($score >= 4) $diplomaTitle = '👍 Bra jobbat!';

    $redirectUrl = $wantMember ? 'https://moderaterna.membersite.se/Membership/BuyMembership' : 'index.html';

    jsonOut([
        'success' => true,
        'score' => $score,
        'totalQuestions' => 8,
        'diplomaTitle' => $diplomaTitle,
        'wantMember' => (bool)$wantMember,
        'redirectUrl' => $redirectUrl
    ]);
}

// 3. POST /api/quiz/admin/login
if (($route === 'admin/login' || $route === 'quiz/admin/login') && $method === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $_POST;
    $pwd = $body['password'] ?? '';

    if ($pwd !== $adminPassword) {
        jsonOut(['error' => 'Felaktigt administratörslösenord.'], 401);
    }

    $payloadData = ['role' => 'admin', 'exp' => time() + 86400 * 7];
    $payloadB64 = base64_encode(json_encode($payloadData));
    $sig = hash_hmac('sha256', $payloadB64, $secretKey);
    $token = "{$payloadB64}.{$sig}";

    setcookie('quiz_admin_token', $token, [
        'expires' => time() + 86400 * 7,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    jsonOut(['success' => true, 'token' => $token]);
}

// 4. POST /api/quiz/admin/logout
if (($route === 'admin/logout' || $route === 'quiz/admin/logout') && $method === 'POST') {
    setcookie('quiz_admin_token', '', time() - 3600, '/');
    jsonOut(['success' => true]);
}

// 5. GET /api/quiz/admin/submissions
if (($route === 'admin/submissions' || $route === 'quiz/admin/submissions') && $method === 'GET') {
    if (!checkAuth()) jsonOut(['error' => 'Obehörig åtkomst.'], 401);

    $pdo = getDB();
    if (!$pdo) jsonOut(['error' => 'Kunde inte ansluta till databasen.'], 500);

    $filter = $_GET['filter'] ?? 'all';
    $search = trim($_GET['search'] ?? '');
    $sortBy = $_GET['sortBy'] ?? 'created_at';
    $sortDir = strtoupper($_GET['sortDir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $allowedCols = ['created_at', 'name', 'city', 'score', 'tiebreaker_guess', 'source'];
    if (!in_array($sortBy, $allowedCols)) $sortBy = 'created_at';

    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(want_member) as wantMember,
            SUM(want_info) as wantInfo,
            SUM(CASE WHEN score = 8 THEN 1 ELSE 0 END) as perfectScores,
            SUM(CASE WHEN source = 'kexchoklad' THEN 1 ELSE 0 END) as fromKexchoklad,
            ROUND(AVG(score), 1) as avgScore
        FROM quiz_submissions
    ");
    $stats = $statsStmt->fetch() ?: [
        'total' => 0, 'wantMember' => 0, 'wantInfo' => 0, 'perfectScores' => 0, 'fromKexchoklad' => 0, 'avgScore' => '0.0'
    ];

    // Source breakdown for dynamic buttons
    $sourcesStmt = $pdo->query("SELECT source, COUNT(*) as count FROM quiz_submissions GROUP BY source ORDER BY count DESC");
    $stats['sources'] = $sourcesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $submissions = [];

    if ($filter === 'winners_top3') {
        $totalCount = (int)($stats['total'] ?? 0);
        $searchSql = "";
        $searchParams = [];
        if ($search !== '') {
            $searchSql = "AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR city LIKE ?)";
            $searchParams = ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        }
        $stmtPerfect = $pdo->prepare("SELECT id, created_at, source, name, city, phone, email, score, total_questions, tiebreaker_guess, prize_choice, want_info, want_member FROM quiz_submissions WHERE score = 8 {$searchSql}");
        $stmtPerfect->execute($searchParams);
        $perfectRows = $stmtPerfect->fetchAll();

        usort($perfectRows, function($a, $b) use ($totalCount) {
            $diffA = abs((int)$a['tiebreaker_guess'] - $totalCount);
            $diffB = abs((int)$b['tiebreaker_guess'] - $totalCount);
            if ($diffA === $diffB) {
                return strcmp($a['created_at'], $b['created_at']);
            }
            return $diffA <=> $diffB;
        });
        $submissions = array_slice($perfectRows, 0, 3);
    } else {
        $where = [];
        $params = [];

        if ($filter === 'member') {
            $where[] = "want_member = 1";
        } elseif ($filter === 'info') {
            $where[] = "want_info = 1";
        } elseif ($filter === 'perfect') {
            $where[] = "score = 8";
        } elseif (strpos($filter, 'source:') === 0) {
            $where[] = "source = ?";
            $params[] = substr($filter, 7);
        } elseif ($filter !== 'all') {
            $where[] = "source = ?";
            $params[] = $filter;
        }

        if ($search !== '') {
            $where[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR city LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereSql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : "";
        $stmt = $pdo->prepare("SELECT id, created_at, source, name, city, phone, email, score, total_questions, tiebreaker_guess, prize_choice, want_info, want_member FROM quiz_submissions {$whereSql} ORDER BY {$sortBy} {$sortDir}");
        $stmt->execute($params);
        $submissions = $stmt->fetchAll();
    }

    jsonOut(['success' => true, 'submissions' => $submissions, 'stats' => $stats]);
}

// 6. GET /api/quiz/admin/export (CSV)
if (($route === 'admin/export' || $route === 'quiz/admin/export') && $method === 'GET') {
    if (!checkAuth()) jsonOut(['error' => 'Obehörig åtkomst.'], 401);

    $pdo = getDB();
    if (!$pdo) jsonOut(['error' => 'Databasfel'], 500);

    $stmt = $pdo->query("SELECT * FROM quiz_submissions ORDER BY created_at DESC");
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ale-quiz-deltagare-' . date('Y-m-d') . '.csv"');

    // UTF-8 BOM för Excel
    echo "\xEF\xBB\xBF";
    echo "ID;Datum;Källa;Namn;Hemort;Telefon;E-post;Poäng;Totalt;Utslagsgissning;Önskat pris;Vill ha info;Vill bli medlem\r\n";

    foreach ($rows as $r) {
        $info = !empty($r['want_info']) ? 'Ja' : 'Nej';
        $member = !empty($r['want_member']) ? 'Ja' : 'Nej';
        $city = $r['city'] ?? '';
        echo "{$r['id']};{$r['created_at']};{$r['source']};"{$r['name']}";"{$city}";"{$r['phone']}";"{$r['email']}";{$r['score']};{$r['total_questions']};{$r['tiebreaker_guess']};"{$r['prize_choice']}";{$info};{$member}\r\n";
    }
    exit;
}

// 7. DELETE /api/quiz/admin/submission/:id
if (preg_match('#^(?:quiz/)?admin/submission/(\d+)$#', $route, $m) && $method === 'DELETE') {
    if (!checkAuth()) jsonOut(['error' => 'Obehörig åtkomst.'], 401);

    $id = intval($m[1]);
    $pdo = getDB();
    if ($pdo) {
        $stmt = $pdo->prepare("DELETE FROM quiz_submissions WHERE id = ?");
        $stmt->execute([$id]);
        jsonOut(['success' => true]);
    }
    jsonOut(['error' => 'Databasfel'], 500);
}

// Okänd route
jsonOut(['error' => 'Endpoint hittades inte'], 404);
