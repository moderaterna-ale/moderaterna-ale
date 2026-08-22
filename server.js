/**
 * Express Server för Ale-quizet (ale.nu/quiz)
 * Moderaterna i Ale (ale.nu)
 */

const express = require('express');
const path = require('path');
const cors = require('cors');
const cookieParser = require('cookie-parser');
const jwt = require('jsonwebtoken');
require('dotenv').config();

const { initDatabase, saveSubmission, getSubmissions, getStats, deleteSubmission } = require('./database');
const { getClientQuestions, gradeSubmission } = require('./questions');

const app = express();
const PORT = process.env.PORT || 3001;
const JWT_SECRET = process.env.JWT_SECRET || 'ale_moderaterna_quiz_secret_key_2026';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Ale-M2026!SuperK0mmun#982';

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(cookieParser());

// Statiska filer från rotmappen
app.use(express.static(path.join(__dirname)));

// Auth Middleware för Admin
function authenticateAdmin(req, res, next) {
  const token = req.cookies.admin_token || req.headers.authorization?.replace('Bearer ', '');
  if (!token) {
    return res.status(401).json({ error: 'Ej auktoriserad. Logga in.' });
  }

  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    if (decoded && decoded.role === 'admin') {
      req.admin = decoded;
      return next();
    }
    return res.status(401).json({ error: 'Ogiltig session.' });
  } catch (err) {
    return res.status(401).json({ error: 'Sessionen har gått ut. Logga in igen.' });
  }
}

/* ==========================================================================
   Publika Rutter (Quiz)
   ========================================================================== */

// Snygga URL:er
app.get('/quiz', (req, res) => {
  res.sendFile(path.join(__dirname, 'quiz.html'));
});

app.get('/quiz-admin', (req, res) => {
  res.sendFile(path.join(__dirname, 'quiz-admin.html'));
});

// Hämta slumpade frågor till klienten
app.get('/api/quiz/questions', (req, res) => {
  try {
    const clientQuestions = getClientQuestions();
    res.json({
      success: true,
      totalQuestions: clientQuestions.length,
      questions: clientQuestions
    });
  } catch (err) {
    console.error('Fel vid hämtning av frågor:', err);
    res.status(500).json({ error: 'Kunde inte ladda frågorna.' });
  }
});

// Skicka in tävlingsbidrag
app.post('/api/quiz/submit', async (req, res) => {
  try {
    const {
      source,
      name,
      city,
      phone,
      email,
      answers,
      tiebreakerGuess,
      prizeChoice,
      wantInfo,
      wantMember
    } = req.body;

    if (!name || !phone || !email || !city) {
      return res.status(400).json({ error: 'Vänligen fyll i namn, hemort, telefon och e-postadress.' });
    }

    if (tiebreakerGuess === undefined || tiebreakerGuess === null || isNaN(tiebreakerGuess)) {
      return res.status(400).json({ error: 'Vänligen ange din gissning på utslagsfrågan.' });
    }

    if (!prizeChoice) {
      return res.status(400).json({ error: 'Vänligen välj vilket pris du föredrar att vinna.' });
    }

    // Rätta svar serverside
    const gradeResult = gradeSubmission(answers);

    // IP & User-Agent
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress || '';
    const userAgent = req.headers['user-agent'] || '';

    // Spara i databasen
    const insertId = await saveSubmission({
      source: source || 'kexchoklad',
      name: name.trim(),
      city: city.trim(),
      phone: phone.trim(),
      email: email.trim().toLowerCase(),
      score: gradeResult.score,
      totalQuestions: gradeResult.totalQuestions,
      tiebreakerGuess: parseInt(tiebreakerGuess, 10),
      prizeChoice: prizeChoice,
      wantInfo: Boolean(wantInfo),
      wantMember: Boolean(wantMember),
      answersJson: JSON.stringify(gradeResult.details),
      ipAddress: String(ipAddress).slice(0, 45),
      userAgent: String(userAgent)
    });

    // Diplom-titel baserat på poäng
    let diplomaTitle = 'Bra kämpat!';
    let diplomaBadge = '🎖️';
    if (gradeResult.score === 8) {
      diplomaTitle = 'Full pott – En sann Ale-expert!';
      diplomaBadge = '🏆';
    } else if (gradeResult.score >= 6) {
      diplomaTitle = 'Mycket väl godkänd – Riktig Ale-kännare!';
      diplomaBadge = '🥇';
    } else if (gradeResult.score >= 4) {
      diplomaTitle = 'Godkänt resultat!';
      diplomaBadge = '🥈';
    }

    // Bestäm vart användaren ska slussas
    const redirectUrl = wantMember 
      ? 'https://moderaterna.membersite.se/Membership/BuyMembership'
      : 'index.html';

    res.json({
      success: true,
      submissionId: insertId,
      score: gradeResult.score,
      totalQuestions: gradeResult.totalQuestions,
      diplomaTitle,
      diplomaBadge,
      wantMember: Boolean(wantMember),
      redirectUrl,
      details: gradeResult.details
    });
  } catch (err) {
    console.error('Fel vid inlämning av quiz:', err);
    res.status(500).json({ error: 'Ett internt fel uppstod vid sparandet. Försök igen.' });
  }
});

/* ==========================================================================
   Admin Rutter (quiz-admin)
   ========================================================================== */

// Admin Inloggning
app.post('/api/quiz/admin/login', (req, res) => {
  const { password } = req.body;
  if (!password || password !== ADMIN_PASSWORD) {
    return res.status(401).json({ error: 'Felaktigt administratörslösenord.' });
  }

  const token = jwt.sign({ role: 'admin', loggedInAt: Date.now() }, JWT_SECRET, { expiresIn: '7d' });

  // Sätt HttpOnly cookie
  res.cookie('admin_token', token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    maxAge: 7 * 24 * 60 * 60 * 1000 // 7 dagar
  });

  res.json({ success: true, token });
});

// Admin Logout
app.post('/api/quiz/admin/logout', (req, res) => {
  res.clearCookie('admin_token');
  res.json({ success: true });
});

// Hämta inlägg och statistik
app.get('/api/quiz/admin/submissions', authenticateAdmin, async (req, res) => {
  try {
    const { filter = 'all', search = '', sortBy = 'created_at', sortDir = 'DESC' } = req.query;
    const submissions = await getSubmissions(filter, search, sortBy, sortDir);
    const stats = await getStats();

    res.json({
      success: true,
      stats,
      submissions
    });
  } catch (err) {
    console.error('Fel vid hämtning av admin-data:', err);
    res.status(500).json({ error: 'Kunde inte hämta data.' });
  }
});

// Exportera CSV för Excel
app.get('/api/quiz/admin/export', authenticateAdmin, async (req, res) => {
  try {
    const submissions = await getSubmissions('all', '', 'created_at', 'DESC');

    // CSV Header med UTF-8 BOM (\uFEFF) så att Excel på Windows öppnar å, ä, ö felfritt
    let csv = '\uFEFFID;Datum & Tid;Källa;Namn;Hemort;Telefon;E-post;Poäng;Totalt;Utslagsgissning;Önskad Vinst;Vill ha info;Vill bli medlem\r\n';

    for (const s of submissions) {
      const dateStr = s.created_at ? new Date(s.created_at).toLocaleString('sv-SE') : '';
      const wantInfoStr = s.want_info ? 'Ja' : 'Nej';
      const wantMemberStr = s.want_member ? 'Ja' : 'Nej';
      
      const clean = (str) => `"${String(str || '').replace(/"/g, '""')}"`;

      csv += [
        s.id,
        clean(dateStr),
        clean(s.source),
        clean(s.name),
        clean(s.city),
        clean(s.phone),
        clean(s.email),
        s.score,
        s.total_questions,
        s.tiebreaker_guess,
        clean(s.prize_choice),
        wantInfoStr,
        wantMemberStr
      ].join(';') + '\r\n';
    }

    const filename = `ale_quiz_deltagare_${new Date().toISOString().slice(0, 10)}.csv`;
    res.setHeader('Content-Type', 'text/csv; charset=utf-8');
    res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
    res.send(csv);
  } catch (err) {
    console.error('Fel vid CSV-export:', err);
    res.status(500).send('Kunde inte generera CSV.');
  }
});

// Radera ett bidrag
app.delete('/api/quiz/admin/submission/:id', authenticateAdmin, async (req, res) => {
  try {
    const id = parseInt(req.params.id, 10);
    const success = await deleteSubmission(id);
    if (success) {
      res.json({ success: true, message: 'Bidraget har raderats.' });
    } else {
      res.status(404).json({ error: 'Bidraget hittades inte.' });
    }
  } catch (err) {
    console.error('Fel vid radering av bidrag:', err);
    res.status(500).json({ error: 'Kunde inte radera bidraget.' });
  }
});

// Starta servern
const server = app.listen(PORT, () => {
  console.log(`🚀 Ale Quiz Server körs på port ${PORT}`);
  console.log(`👉 Quiz URL: /quiz`);
  console.log(`👉 Admin URL: /quiz-admin`);
});

// Initiera databasanslutning
initDatabase().catch(err => {
  console.error('❌ Databasinitieringsfel:', err);
});

module.exports = app;
