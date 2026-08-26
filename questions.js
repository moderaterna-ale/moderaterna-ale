/**
 * Frågor för Ale-quizet (ale.nu/quiz)
 * 8 frågor: 4 allmänna/lokala frågor + 4 politiska profilfrågor
 */

const questions = [
  {
    id: 1,
    category: 'Allmän',
    question: 'Alependeln är en av Västsveriges snabbaste pendellinjer. Hur många minuter tar det ungefär med tåget från Göteborgs Central till Älvängen?',
    options: [
      { id: 'opt_1', text: '27 minuter', isCorrect: true },
      { id: 'opt_2', text: '14 minuter', isCorrect: false },
      { id: 'opt_3', text: '58 minuter (om inte en ko står på spåret)', isCorrect: false }
    ],
    explanation: 'Med Alependeln tar det bara ca 27 minuter från Göteborg C till Älvängen – snabbt, smidigt och klimatsmart i vardagen!'
  },
  {
    id: 2,
    category: 'Allmän',
    question: 'Vilket unikt föremål hittades i Ale 1933?',
    options: [
      { id: 'opt_1', text: 'Vikingaskepp', isCorrect: true },
      { id: 'opt_2', text: 'UFO', isCorrect: false },
      { id: 'opt_3', text: 'Kista med guld', isCorrect: false }
    ],
    explanation: 'Äskekärrskeppet, Sveriges enda bevarade vikingaskepp (knarr), grävdes fram i Äskekärr i Ale 1933!'
  },
  {
    id: 3,
    category: 'Allmän',
    question: 'Vilket vidsträckt och mytomspunnet vildmarksområde med hundratals sjöar och fina vandringsleder breder ut sig i östra delen av Ale kommun?',
    options: [
      { id: 'opt_1', text: 'Risveden', isCorrect: true },
      { id: 'opt_2', text: 'Svartedalen', isCorrect: false },
      { id: 'opt_3', text: 'Sandsjöbacka', isCorrect: false }
    ],
    explanation: 'Risveden är ett av Västsveriges största sammanhängande vildmarksområden och bjuder på fantastiska naturupplevelser, bad och leder för alla Alebor!'
  },
  {
    id: 4,
    category: 'Profil',
    question: 'Psykisk ohälsa är ett stort problem, Moderaternas lösning är?',
    options: [
      { id: 'opt_1', text: 'Rätt stöd när du behöver det', isCorrect: true },
      { id: 'opt_2', text: 'Fler blanketter att fylla i', isCorrect: false },
      { id: 'opt_3', text: 'Saft och bulle', isCorrect: false }
    ],
    explanation: 'Moderaterna vill att stöd och insatser ska ges tidigt, utan krångel och med fokus på individens behov när man behöver det.'
  },
  {
    id: 5,
    category: 'Profil',
    question: 'Moderaterna i Ale har en tydlig och ambitiös målsättning för kommunens utveckling. Vad är målet senast år 2030?',
    options: [
      { id: 'opt_1', text: 'Bli nominerade till ”Årets Superkommun” med service och ekonomi i toppklass', isCorrect: true },
      { id: 'opt_2', text: 'Bygga Sveriges högsta skyskrapa i Skepplanda', isCorrect: false },
      { id: 'opt_3', text: 'Byta ut pendeltågen mot linbana', isCorrect: false }
    ],
    explanation: 'Med en stabil ekonomi, sänkt skatt och fokus på välfärdens kärna vill Moderaterna göra Ale till en certifierad Superkommun senast 2030!'
  },
  {
    id: 6,
    category: 'Profil',
    question: 'Vad tycker Moderaterna om en bilväg mellan Lövgärdet och Surte?',
    options: [
      { id: 'opt_1', text: 'Vi säger bestämt NEJ – vi vill bevara Surtes unika karaktär, lugn och trygghet!', isCorrect: true },
      { id: 'opt_2', text: 'Vi tycker det låter som ett fantastiskt rallyspår', isCorrect: false },
      { id: 'opt_3', text: 'Vi vill bygga en åttafilad motorväg genom badsjön', isCorrect: false }
    ],
    explanation: 'Moderaterna sätter Surtebornas trygghet, miljö och lokala karaktär först. Ingen bilväg till Lövgärdet!'
  },
  {
    id: 7,
    category: 'Profil',
    question: 'Hur vill Moderaterna utveckla skolan i Ale?',
    options: [
      { id: 'opt_1', text: 'Skolfrukost, kött varje dag, fler vuxna', isCorrect: true },
      { id: 'opt_2', text: 'Inga lov', isCorrect: false },
      { id: 'opt_3', text: '100 elever i varje klass', isCorrect: false }
    ],
    explanation: 'Genom satsningar på skolfrukost, bra näringsrik mat med valmöjligheter och fler vuxna i skolan skapar vi trygghet och studiero så att varje elev kan nå sin fulla potential!'
  },
  {
    id: 8,
    category: 'Profil',
    question: 'Ale har något som många kommuner avundas – milslång älvkontakt. Vad vill Moderaterna göra med området längs Göta älv?',
    options: [
      { id: 'opt_1', text: 'Skapa liv, rörelse och aktivitet', isCorrect: true },
      { id: 'opt_2', text: 'Bygga en fem meter hög betongmur', isCorrect: false },
      { id: 'opt_3', text: 'Förbjuda all form av vistelse vid vattnet', isCorrect: false }
    ],
    explanation: 'Moderaterna vill öppna upp älvstråket med promenadstråk, bryggor och mötesplatser så att alla Alebor kan njuta av närheten till vattnet!'
  }
];

function shuffleArray(array) {
  const arr = [...array];
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}

/**
 * Returnerar en slumpad frågeuppsättning för användaren (utan att avslöja isCorrect)
 * Fråga 1 är alltid en välkomnande allmän fråga (t.ex. tågresan eller Göta älv).
 */
function getClientQuestions() {
  const q1 = questions[0]; // Tågresan som första välkomnande fråga
  const remaining = questions.slice(1);
  const shuffledRemaining = shuffleArray(remaining);
  const orderedQuestions = [q1, ...shuffledRemaining];

  return orderedQuestions.map(q => ({
    id: q.id,
    category: q.category,
    question: q.question,
    options: shuffleArray(q.options).map(opt => ({
      id: opt.id,
      text: opt.text
    }))
  }));
}

/**
 * Rättar inskickade svar och beräknar poäng
 */
function gradeSubmission(answers) {
  let score = 0;
  const details = [];

  for (const q of questions) {
    const userOptionId = answers[q.id];
    const correctOpt = q.options.find(o => o.isCorrect);
    const userOpt = q.options.find(o => o.id === userOptionId);
    const isCorrect = userOptionId === correctOpt.id;

    if (isCorrect) score++;

    details.push({
      questionId: q.id,
      question: q.question,
      userOptionId: userOptionId || null,
      userAnswerText: userOpt ? userOpt.text : 'Inget svar',
      correctOptionId: correctOpt.id,
      correctAnswerText: correctOpt.text,
      isCorrect,
      explanation: q.explanation
    });
  }

  return {
    score,
    totalQuestions: questions.length,
    details
  };
}

module.exports = {
  questions,
  getClientQuestions,
  gradeSubmission
};
