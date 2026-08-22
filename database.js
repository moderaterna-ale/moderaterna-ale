/**
 * Databashanterare för Ale-quizet
 * Stöder både MariaDB / MySQL (produktion på Oderland) och SQLite (lokal utveckling/fallback)
 */

const path = require('path');
const fs = require('fs');
require('dotenv').config();

let dbType = 'sqlite';
let mysqlPool = null;
let sqliteDb = null;

// Initialisera databasanslutning
async function initDatabase() {
  const useMySQL = process.env.DB_NAME && process.env.DB_USER;

  if (useMySQL) {
    try {
      const mysql = require('mysql2/promise');
      mysqlPool = mysql.createPool({
        host: process.env.DB_HOST || 'localhost',
        port: parseInt(process.env.DB_PORT || '3306', 10),
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME,
        charset: 'utf8mb4',
        waitForConnections: true,
        connectionLimit: 10,
        queueLimit: 0
      });

      // Testa anslutning och skapa tabell
      const conn = await mysqlPool.getConnection();
      console.log('✅ Ansluten till MariaDB/MySQL på', process.env.DB_HOST);

      await conn.query(`
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
      `);

      // Migration för befintlig tabell om city saknas
      try {
        await conn.query(`ALTER TABLE quiz_submissions ADD COLUMN city VARCHAR(100) DEFAULT '' AFTER name;`);
      } catch (e) {
        // Ignorera om kolumnen redan finns
      }

      conn.release();
      dbType = 'mysql';
      return;
    } catch (err) {
      console.warn('⚠️ Kunde inte ansluta till MariaDB/MySQL. Faller tillbaka till lokal SQLite:', err.message);
    }
  }

  // Fallback till SQLite
  const dataDir = path.join(__dirname, 'data');
  if (!fs.existsSync(dataDir)) {
    fs.mkdirSync(dataDir, { recursive: true });
  }

  const sqlite3 = require('sqlite3').verbose();
  const dbPath = path.join(dataDir, 'quiz.db');
  
  return new Promise((resolve, reject) => {
    sqliteDb = new sqlite3.Database(dbPath, (err) => {
      if (err) {
        console.error('❌ Fel vid initiering av SQLite:', err);
        return reject(err);
      }
      console.log('✅ Ansluten till lokal SQLite-databas:', dbPath);

      sqliteDb.run(`
        CREATE TABLE IF NOT EXISTS quiz_submissions (
          id INTEGER PRIMARY KEY,
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
        )
      `, (err) => {
        if (err) return reject(err);

        // Migration för SQLite om kolumnen city saknas
        sqliteDb.run(`ALTER TABLE quiz_submissions ADD COLUMN city TEXT DEFAULT ''`, () => {
          dbType = 'sqlite';
          resolve();
        });
      });
    });
  });
}

/**
 * Sparar ett tävlingsbidrag
 */
async function saveSubmission(data) {
  const {
    source = 'kexchoklad',
    name,
    city = '',
    phone,
    email,
    score,
    totalQuestions = 8,
    tiebreakerGuess,
    prizeChoice,
    wantInfo = false,
    wantMember = false,
    answersJson = '{}',
    ipAddress = '',
    userAgent = ''
  } = data;

  const infoVal = wantInfo ? 1 : 0;
  const memberVal = wantMember ? 1 : 0;

  if (dbType === 'mysql') {
    const query = `
      INSERT INTO quiz_submissions 
      (source, name, city, phone, email, score, total_questions, tiebreaker_guess, prize_choice, want_info, want_member, answers_json, ip_address, user_agent)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `;
    const [result] = await mysqlPool.execute(query, [
      source, name, city, phone, email, score, totalQuestions, tiebreakerGuess, prizeChoice, infoVal, memberVal, answersJson, ipAddress, userAgent
    ]);
    return result.insertId;
  } else {
    return new Promise((resolve, reject) => {
      const query = `
        INSERT INTO quiz_submissions 
        (source, name, city, phone, email, score, total_questions, tiebreaker_guess, prize_choice, want_info, want_member, answers_json, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `;
      sqliteDb.run(query, [
        source, name, city, phone, email, score, totalQuestions, tiebreakerGuess, prizeChoice, infoVal, memberVal, answersJson, ipAddress, userAgent
      ], function(err) {
        if (err) return reject(err);
        resolve(this.lastID);
      });
    });
  }
}

/**
 * Hämtar alla bidrag med filter och sortering
 */
async function getSubmissions(filter = 'all', search = '', sortBy = 'created_at', sortDir = 'DESC') {
  const validSortCols = ['created_at', 'score', 'tiebreaker_guess', 'name', 'city'];
  const sortCol = validSortCols.includes(sortBy) ? sortBy : 'created_at';
  const order = sortDir.toUpperCase() === 'ASC' ? 'ASC' : 'DESC';

  let whereClauses = [];
  let params = [];

  if (filter === 'member') {
    whereClauses.push('want_member = 1');
  } else if (filter === 'info') {
    whereClauses.push('want_info = 1');
  } else if (filter === 'perfect') {
    whereClauses.push('score = 8');
  } else if (filter === 'kexchoklad') {
    whereClauses.push("source = 'kexchoklad'");
  }

  if (search && search.trim()) {
    const s = `%${search.trim()}%`;
    whereClauses.push('(name LIKE ? OR city LIKE ? OR email LIKE ? OR phone LIKE ?)');
    params.push(s, s, s, s);
  }

  const whereSql = whereClauses.length > 0 ? `WHERE ${whereClauses.join(' AND ')}` : '';

  if (dbType === 'mysql') {
    const query = `SELECT * FROM quiz_submissions ${whereSql} ORDER BY ${sortCol} ${order}`;
    const [rows] = await mysqlPool.execute(query, params);
    return rows;
  } else {
    return new Promise((resolve, reject) => {
      const query = `SELECT * FROM quiz_submissions ${whereSql} ORDER BY ${sortCol} ${order}`;
      sqliteDb.all(query, params, (err, rows) => {
        if (err) return reject(err);
        resolve(rows || []);
      });
    });
  }
}

/**
 * Hämtar statistik
 */
async function getStats() {
  if (dbType === 'mysql') {
    const [totalRows] = await mysqlPool.query('SELECT COUNT(*) as total, AVG(score) as avgScore FROM quiz_submissions');
    const [memberRows] = await mysqlPool.query('SELECT COUNT(*) as count FROM quiz_submissions WHERE want_member = 1');
    const [infoRows] = await mysqlPool.query('SELECT COUNT(*) as count FROM quiz_submissions WHERE want_info = 1');
    const [kexRows] = await mysqlPool.query("SELECT COUNT(*) as count FROM quiz_submissions WHERE source = 'kexchoklad'");
    const [perfectRows] = await mysqlPool.query('SELECT COUNT(*) as count FROM quiz_submissions WHERE score = 8');

    return {
      total: totalRows[0].total || 0,
      avgScore: totalRows[0].avgScore ? parseFloat(totalRows[0].avgScore).toFixed(1) : '0',
      wantMember: memberRows[0].count || 0,
      wantInfo: infoRows[0].count || 0,
      fromKexchoklad: kexRows[0].count || 0,
      perfectScores: perfectRows[0].count || 0,
      dbType
    };
  } else {
    return new Promise((resolve, reject) => {
      sqliteDb.get(`
        SELECT 
          COUNT(*) as total,
          AVG(score) as avgScore,
          SUM(CASE WHEN want_member = 1 THEN 1 ELSE 0 END) as wantMember,
          SUM(CASE WHEN want_info = 1 THEN 1 ELSE 0 END) as wantInfo,
          SUM(CASE WHEN source = 'kexchoklad' THEN 1 ELSE 0 END) as fromKexchoklad,
          SUM(CASE WHEN score = 8 THEN 1 ELSE 0 END) as perfectScores
        FROM quiz_submissions
      `, (err, row) => {
        if (err) return reject(err);
        resolve({
          total: row ? (row.total || 0) : 0,
          avgScore: (row && row.avgScore) ? parseFloat(row.avgScore).toFixed(1) : '0',
          wantMember: row ? (row.wantMember || 0) : 0,
          wantInfo: row ? (row.wantInfo || 0) : 0,
          fromKexchoklad: row ? (row.fromKexchoklad || 0) : 0,
          perfectScores: row ? (row.perfectScores || 0) : 0,
          dbType
        });
      });
    });
  }
}

/**
 * Raderar ett bidrag (t.ex. test-inlägg)
 */
async function deleteSubmission(id) {
  if (dbType === 'mysql') {
    const [result] = await mysqlPool.execute('DELETE FROM quiz_submissions WHERE id = ?', [id]);
    return result.affectedRows > 0;
  } else {
    return new Promise((resolve, reject) => {
      sqliteDb.run('DELETE FROM quiz_submissions WHERE id = ?', [id], function(err) {
        if (err) return reject(err);
        resolve(this.changes > 0);
      });
    });
  }
}

module.exports = {
  initDatabase,
  saveSubmission,
  getSubmissions,
  getStats,
  deleteSubmission
};
