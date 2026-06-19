const state = {
  currentView: "overview",
  lesson: "intro",
  wave: "alpha",
  caseIndex: 0,
  channel: "FP1-F7",
  segment: 34,
  selectedAnswer: null,
  challengeIndex: 0,
  challengeScore: 0,
  answeredChallenges: {},
  skill: 0,
  activeQuizType: null,
  activeQuizIndex: 0,
  activeQuizCorrect: 0,
  quizScores: {
    pre: null,
    post: null,
    case: null
  }
};

const channels = ["FP1-F7", "F7-T7", "T7-P7", "P7-O1", "FP2-F8", "F8-T8", "T8-P8", "P8-O2", "FZ-CZ", "CZ-PZ"];

const lessons = {
  intro: {
    title: "Apa itu EEG?",
    text: "Electroencephalogram merekam aktivitas listrik otak melalui elektroda di kulit kepala. Dalam pembelajaran klinis, EEG membantu mahasiswa menghubungkan pola sinyal dengan gejala pasien, kondisi neurologis, dan keputusan diagnosis."
  },
  waves: {
    title: "Gelombang otak",
    text: "EEG tersusun dari ritme dengan rentang frekuensi berbeda. Alpha sering terkait kondisi rileks, beta dengan aktivitas kognitif, theta dengan mengantuk atau perkembangan anak, dan delta dengan tidur dalam atau disfungsi tertentu."
  },
  electrodes: {
    title: "Elektroda dan kanal",
    text: "Kanal EEG membaca beda potensial antar elektroda. Sistem 10-20 membantu penempatan elektroda secara konsisten, sehingga mahasiswa dapat membandingkan aktivitas frontal, temporal, parietal, dan oksipital."
  },
  disorders: {
    title: "Gangguan neurologis",
    text: "Pada epilepsi, EEG dapat menunjukkan aktivitas epileptiform seperti spike, sharp wave, atau spike-wave. Interpretasi tetap harus dikaitkan dengan riwayat klinis, gejala, dan konteks pasien."
  }
};

const waves = {
  alpha: { name: "Alpha", info: "8-13 Hz, sering muncul saat rileks.", color: "#05a7b8", hz: 10 },
  beta: { name: "Beta", info: "13-30 Hz, terkait fokus dan aktivitas kognitif.", color: "#2d9d78", hz: 22 },
  theta: { name: "Theta", info: "4-8 Hz, dapat muncul saat mengantuk.", color: "#d8902f", hz: 6 },
  delta: { name: "Delta", info: "0.5-4 Hz, dominan saat tidur dalam.", color: "#e45858", hz: 2 }
};

const cases = [
  {
    title: "CHB-MIT Case A",
    patient: "Anak 9 tahun",
    complaint: "Episode tatapan kosong 20-40 detik, bingung setelah kejadian.",
    condition: "Segmen temporal kiri menunjukkan spike-wave berulang.",
    confidence: 82,
    explainWindow: { start: 420, width: 168, label: "spike-wave temporal" },
    diagnosis: "Aktivitas epileptiform fokal",
    reasons: [
      "Amplitudo meningkat tajam pada kanal temporal.",
      "Pola spike-wave muncul berulang pada rentang 3-4 Hz.",
      "Perubahan sinyal beririsan dengan laporan gejala klinis."
    ],
    explain: "Model menandai segmen beramplitudo tinggi dan transisi tajam sebagai area penting. Dalam konteks edukasi, pola tersebut perlu dibaca sebagai petunjuk epileptiform, lalu dikonfirmasi melalui informasi klinis pasien."
  },
  {
    title: "CHB-MIT Case B",
    patient: "Anak 12 tahun",
    complaint: "Riwayat kejang malam, kelelahan setelah bangun.",
    condition: "Dominasi gelombang lambat dengan artefak gerakan ringan.",
    confidence: 47,
    explainWindow: { start: 210, width: 132, label: "artefak gerakan" },
    diagnosis: "Artefak dan tidur ringan",
    reasons: [
      "Aktivitas lambat relatif menyebar dan tidak tajam.",
      "Puncak sinyal tidak konsisten antar kanal.",
      "Sebagian pola cocok dengan transisi tidur dan gerakan."
    ],
    explain: "Model memberi keyakinan sedang karena ada pola lambat, tetapi tidak cukup bukti untuk menyimpulkan epileptiform. Mahasiswa perlu memisahkan artefak, fase tidur, dan pola patologis."
  },
  {
    title: "CHB-MIT Case C",
    patient: "Anak 7 tahun",
    complaint: "Gerakan ritmis pada tangan kanan disertai penurunan respons.",
    condition: "Discharge ritmis meningkat pada kanal frontal-sentral.",
    confidence: 74,
    explainWindow: { start: 610, width: 190, label: "evolusi ritmis" },
    diagnosis: "Kecurigaan seizure onset frontal",
    reasons: [
      "Ritme meningkat bertahap dari baseline.",
      "Area frontal-sentral memiliki kontribusi perhatian model tertinggi.",
      "Pola temporal sesuai dengan onset gejala motorik."
    ],
    explain: "AI memusatkan perhatian pada perubahan ritmis yang berkembang, bukan hanya satu puncak amplitudo. Ini membantu mahasiswa melihat bahwa diagnosis EEG bergantung pada evolusi pola sepanjang waktu."
  }
];

const challenges = [
  {
    title: "Kasus 1: spike-wave dan tatapan kosong",
    prompt: "Seorang anak mengalami episode tatapan kosong singkat dengan onset dan akhir mendadak. Pada EEG tampak discharge spike-wave generalisata sekitar 3 Hz. Interpretasi edukatif paling sesuai?",
    answers: ["Typical absence seizure", "Artefak gerakan mata", "Gelombang beta normal", "K-complex tidur N2"],
    correct: 0,
    feedback: "Tepat. Literatur EEG dan epilepsi menjelaskan bahwa absence seizure berkaitan dengan gangguan kesadaran singkat dan pola generalized spike-and-wave, klasiknya sekitar 3 Hz.",
    source: "NCBI Bookshelf - Electroencephalography (EEG): An Introductory Text and Atlas; StatPearls/NCBI - Normal EEG Waveforms."
  },
  {
    title: "Kasus 2: puncak tajam saat bergerak",
    prompt: "Sinyal EEG menunjukkan puncak besar yang muncul bersamaan dengan gerakan kepala, tidak konsisten antar kanal, dan tidak memiliki evolusi ritmis. Interpretasi awal terbaik?",
    answers: ["Artefak biologis atau gerakan", "Status epileptikus", "Normal posterior alpha rhythm", "Seizure onset frontal pasti"],
    correct: 0,
    feedback: "Benar. NCBI Bookshelf menekankan bahwa aktivitas dari mata, otot, lidah, jantung, dan sumber lingkungan dapat mengaburkan aktivitas serebral pada EEG.",
    source: "NCBI Bookshelf - Electroencephalography (EEG): An Introductory Text and Atlas, bagian Introduction."
  },
  {
    title: "Kasus 3: discharge epileptiform",
    prompt: "Pada rekaman antar-kejang ditemukan pola spike, spike-and-wave, atau sharp-wave. Dalam konteks pasien dengan kecurigaan epilepsi, apa makna edukatif pola tersebut?",
    answers: ["Dapat menjadi interictal epileptiform discharge", "Selalu berarti pasien sedang tidur normal", "Pasti hanya artefak elektroda", "Tidak pernah berhubungan dengan epilepsi"],
    correct: 0,
    feedback: "Bagus. Teks EEG dari American Epilepsy Society/NCBI menyebut spike, spike-and-wave, dan sharp-wave sebagai bentuk interictal epileptiform discharges yang sering terlihat pada pasien epilepsi.",
    source: "NCBI Bookshelf - Electroencephalography (EEG): An Introductory Text and Atlas, bagian Introduction."
  },
  {
    title: "Kasus 4: data CHB-MIT",
    prompt: "Dataset CHB-MIT di PhysioNet paling tepat digunakan dalam prototipe ini untuk konteks apa?",
    answers: ["Pembelajaran dan evaluasi deteksi onset kejang dari EEG pediatrik", "Diagnosis klinis mandiri tanpa dokter", "Data MRI struktural dewasa", "Rekaman EKG olahraga"],
    correct: 0,
    feedback: "Tepat. PhysioNet menjelaskan CHB-MIT sebagai kumpulan rekaman EEG subjek pediatrik dengan kejang intraktabel, disertai anotasi onset dan akhir kejang.",
    source: "PhysioNet - CHB-MIT Scalp EEG Database v1.0.0."
  }
];

const quizBank = {
  pre: {
    title: "Pre-test dasar EEG",
    source: "NCBI Bookshelf - Electroencephalography (EEG): An Introductory Text and Atlas; StatPearls / NCBI Bookshelf - Normal EEG Waveforms.",
    questions: [
      {
        question: "Menurut teks EEG dari American Epilepsy Society/NCBI, EEG terutama merupakan teknik untuk apa?",
        options: ["Merekam aktivitas listrik yang berasal dari otak", "Mengukur kadar glukosa darah", "Melihat struktur tulang tengkorak", "Menggantikan seluruh pemeriksaan klinis"],
        correct: 0,
        explanation: "EEG adalah teknik elektrofisiologi untuk merekam aktivitas listrik yang berasal dari otak dan berguna untuk evaluasi fungsi serebral dinamis."
      },
      {
        question: "Manakah rentang frekuensi alpha rhythm yang umum digunakan dalam pembelajaran EEG?",
        options: ["8-13 Hz", "13-30 Hz", "0.5-4 Hz", "30-80 Hz"],
        correct: 0,
        explanation: "Alpha rhythm berada sekitar 8-13 Hz dan sering dominan pada kondisi rileks, terutama posterior."
      },
      {
        question: "Mengapa artefak perlu dikenali saat membaca EEG?",
        options: ["Karena aktivitas mata, otot, atau lingkungan dapat mengaburkan sinyal serebral", "Karena semua artefak adalah seizure", "Karena artefak selalu berasal dari korteks", "Karena artefak membuat EEG tidak perlu dianalisis"],
        correct: 0,
        explanation: "Sumber non-serebral seperti mata, otot, lidah, jantung, atau lingkungan dapat memengaruhi rekaman EEG."
      }
    ]
  },
  post: {
    title: "Post-test interpretasi",
    source: "StatPearls / NCBI Bookshelf - Normal EEG Waveforms; NCBI Bookshelf - Electroencephalography (EEG): An Introductory Text and Atlas.",
    questions: [
      {
        question: "Rentang frekuensi beta rhythm yang disebutkan StatPearls/NCBI adalah?",
        options: ["13-30 Hz", "0.5-4 Hz", "4-7 Hz", "8-13 Hz"],
        correct: 0,
        explanation: "StatPearls/NCBI mendeskripsikan beta rhythm pada rentang 13-30 Hz, sering tampak pada region frontal dan sentral."
      },
      {
        question: "Pola spike, spike-and-wave, atau sharp-wave pada pasien epilepsi sering disebut sebagai apa?",
        options: ["Interictal epileptiform discharges", "K-complex fisiologis saja", "Posterior alpha rhythm", "Artefak EKG pasti"],
        correct: 0,
        explanation: "Teks EEG American Epilepsy Society/NCBI menyebut pola tersebut sebagai bentuk interictal epileptiform discharges."
      },
      {
        question: "Apa manfaat Explainable AI untuk pembelajaran EEG?",
        options: ["Membantu menunjukkan segmen/pola yang memengaruhi keputusan model", "Menghapus kebutuhan validasi klinis", "Menjamin diagnosis selalu benar", "Mengubah EEG menjadi MRI"],
        correct: 0,
        explanation: "Dalam konteks edukasi, XAI membantu mahasiswa memahami alasan model, bukan hanya menerima label akhir."
      }
    ]
  },
  case: {
    title: "Evaluasi berbasis kasus",
    source: "PhysioNet - CHB-MIT Scalp EEG Database v1.0.0; NCBI Bookshelf - Electroencephalography (EEG): An Introductory Text and Atlas.",
    questions: [
      {
        question: "Dalam CHB-MIT, file yang mengandung seizure disertai anotasi apa?",
        options: ["Awal dan akhir seizure dalam file anotasi", "Hasil MRI 3D", "Resep obat pasien", "Nilai IQ lengkap pasien"],
        correct: 0,
        explanation: "PhysioNet menjelaskan bahwa awal dan akhir seizure diberi anotasi pada file .seizure dan ringkasan kasus."
      },
      {
        question: "CHB-MIT paling tepat dipakai dalam prototipe ini untuk apa?",
        options: ["Pembelajaran dan evaluasi deteksi onset kejang dari EEG pediatrik", "Diagnosis klinis mandiri tanpa dokter", "Data CT scan dewasa", "Rekaman tekanan darah kontinu"],
        correct: 0,
        explanation: "Dataset ini berisi rekaman EEG pediatrik dengan kejang intraktabel dan anotasi seizure."
      },
      {
        question: "Jika puncak besar muncul bersamaan dengan gerakan dan tidak konsisten antar kanal, langkah awal paling aman adalah?",
        options: ["Pertimbangkan artefak sebelum menyimpulkan seizure", "Langsung menyimpulkan status epileptikus", "Abaikan semua kanal lain", "Menganggapnya gelombang alpha normal"],
        correct: 0,
        explanation: "Interpretasi EEG perlu membedakan aktivitas serebral dari artefak biologis atau lingkungan."
      }
    ]
  }
};

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => Array.from(document.querySelectorAll(selector));

function setView(view) {
  state.currentView = view;
  $$(".view").forEach((item) => item.classList.toggle("active", item.id === view));
  $$(".nav-item").forEach((item) => item.classList.toggle("active", item.dataset.target === view));
  requestAnimationFrame(drawAll);
}

function bindNavigation() {
  $$("[data-target]").forEach((button) => {
    button.addEventListener("click", () => setView(button.dataset.target));
  });
}

function setupLearning() {
  const select = $("#lessonSelect");
  select.addEventListener("change", () => {
    state.lesson = select.value;
    renderLesson();
  });

  const tabs = $("#waveTabs");
  Object.entries(waves).forEach(([key, wave]) => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = `${wave.name} - ${wave.info}`;
    button.addEventListener("click", () => {
      state.wave = key;
      renderLesson();
    });
    tabs.appendChild(button);
  });
  renderLesson();
}

function renderLesson() {
  const lesson = lessons[state.lesson];
  const wave = waves[state.wave];
  $("#lessonTitle").textContent = lesson.title;
  $("#lessonText").textContent = lesson.text;
  $("#waveName").textContent = wave.name;
  $("#waveInfo").textContent = wave.info;
  $$("#waveTabs button").forEach((button, index) => {
    button.classList.toggle("active", Object.keys(waves)[index] === state.wave);
  });
  drawBrain();
}

function setupLab() {
  const channelSelect = $("#channelSelect");
  channels.forEach((channel) => {
    const option = document.createElement("option");
    option.value = channel;
    option.textContent = channel;
    channelSelect.appendChild(option);
  });
  channelSelect.addEventListener("change", () => {
    state.channel = channelSelect.value;
    drawLab();
  });

  $("#segmentRange").addEventListener("input", (event) => {
    state.segment = Number(event.target.value);
    $("#timeMarker").textContent = `0${Math.floor(state.segment / 12)}:${String((state.segment * 4) % 60).padStart(2, "0")}`;
    drawLab();
  });

  const caseSelect = $("#caseSelect");
  cases.forEach((item, index) => {
    const option = document.createElement("option");
    option.value = index;
    option.textContent = item.title;
    caseSelect.appendChild(option);
  });
  caseSelect.addEventListener("change", () => {
    state.caseIndex = Number(caseSelect.value);
    renderCase();
  });
  renderCase();
}

function renderCase() {
  const item = cases[state.caseIndex];
  $("#casePatient").textContent = item.patient;
  $("#caseComplaint").textContent = item.complaint;
  $("#caseCondition").textContent = item.condition;
  $("#caseSelect").value = state.caseIndex;
  renderExplanation();
  drawLab();
}

function setupChallenge() {
  $("#submitAnswerBtn").addEventListener("click", submitAnswer);
  $("#nextCaseBtn").addEventListener("click", () => {
    state.challengeIndex = (state.challengeIndex + 1) % challenges.length;
    state.selectedAnswer = null;
    renderChallenge();
  });
  renderChallenge();
}

function renderChallenge() {
  const item = challenges[state.challengeIndex];
  $("#challengeTitle").textContent = item.title;
  $("#challengePrompt").textContent = item.prompt;
  $("#feedbackText").textContent = "Pilih jawaban diagnosis untuk melihat evaluasi clinical reasoning.";
  $("#challengeSource").textContent = "Sumber akan tampil setelah jawaban dikirim.";
  const list = $("#answerList");
  list.innerHTML = "";
  item.answers.forEach((answer, index) => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "answer-option";
    button.textContent = answer;
    button.addEventListener("click", () => {
      state.selectedAnswer = index;
      $$("#answerList .answer-option").forEach((option) => option.classList.remove("selected"));
      button.classList.add("selected");
    });
    list.appendChild(button);
  });
}

function submitAnswer() {
  const item = challenges[state.challengeIndex];
  if (state.selectedAnswer === null) {
    $("#feedbackText").textContent = "Pilih salah satu jawaban terlebih dahulu.";
    return;
  }
  if (state.answeredChallenges[state.challengeIndex]) {
    $("#feedbackText").textContent = "Kasus ini sudah dinilai. Klik kasus berikutnya untuk melanjutkan latihan.";
    $("#challengeSource").textContent = `Sumber: ${item.source}`;
    return;
  }
  state.answeredChallenges[state.challengeIndex] = true;
  if (state.selectedAnswer === item.correct) {
    state.challengeScore += 10;
    state.skill = Math.min(100, state.skill + 3);
    $("#feedbackText").textContent = item.feedback;
  } else {
    $("#feedbackText").textContent = `Belum tepat. Jawaban yang lebih kuat adalah ${item.answers[item.correct]}. ${item.feedback}`;
  }
  $("#challengeSource").textContent = `Sumber: ${item.source}`;
  $("#challengeScore").textContent = state.challengeScore;
  updateSkill();
}

function setupTutor() {
  $("#chatForm").addEventListener("submit", (event) => {
    event.preventDefault();
    const input = $("#chatInput");
    const question = input.value.trim();
    if (!question) return;
    addMessage(question, "user");
    input.value = "";
    setTimeout(() => addMessage(answerTutor(question), "ai"), 220);
  });
  $$(".quick-prompts button").forEach((button) => {
    button.addEventListener("click", () => {
      $("#chatInput").value = button.dataset.question;
      $("#chatForm").dispatchEvent(new Event("submit"));
    });
  });
}

function addMessage(text, type) {
  const item = document.createElement("div");
  item.className = `message ${type}`;
  item.textContent = text;
  $("#chatPanel").appendChild(item);
  $("#chatPanel").scrollTop = $("#chatPanel").scrollHeight;
}

const tutorIntents = [
  {
    keywords: ["apa itu eeg", "definisi eeg", "eeg itu", "electroencephalogram"],
    answer: "EEG atau electroencephalogram adalah pemeriksaan yang merekam aktivitas listrik otak melalui elektroda di kulit kepala. Dalam pembelajaran NeuroCase, EEG dipakai untuk menghubungkan pola sinyal, gejala pasien, dan clinical reasoning."
  },
  {
    keywords: ["fungsi eeg", "kegunaan eeg", "manfaat eeg", "untuk apa eeg"],
    answer: "EEG berguna untuk mengevaluasi fungsi otak yang dinamis, terutama saat ada kecurigaan seizure, epilepsi, gangguan kesadaran, atau unusual spells. EEG tidak berdiri sendiri; hasilnya tetap harus dikaitkan dengan kondisi klinis pasien."
  },
  {
    keywords: ["alpha", "alfa"],
    answer: "Alpha rhythm berada sekitar 8-13 Hz. Biasanya lebih tampak saat rileks dan sering dominan di area posterior. Saat mata terbuka atau aktivitas mental meningkat, alpha dapat berkurang."
  },
  {
    keywords: ["beta"],
    answer: "Beta rhythm berada sekitar 13-30 Hz dan sering tampak di area frontal-sentral. Aktivitas beta dapat berkaitan dengan kondisi terjaga, fokus, atau efek obat sedatif tertentu."
  },
  {
    keywords: ["theta", "teta"],
    answer: "Theta berada sekitar 4-7 Hz. Theta dapat muncul saat mengantuk atau transisi tidur. Maknanya bergantung pada usia, kondisi sadar, lokasi, dan konteks klinis."
  },
  {
    keywords: ["delta"],
    answer: "Delta berada sekitar 0.5-4 Hz. Delta normal dapat dominan saat tidur dalam, tetapi pada kondisi sadar tertentu aktivitas lambat perlu dievaluasi sebagai kemungkinan disfungsi atau proses patologis."
  },
  {
    keywords: ["frekuensi", "gelombang otak", "alpha beta theta delta", "alfa beta"],
    answer: "Ringkasnya: delta 0.5-4 Hz, theta 4-7 Hz, alpha 8-13 Hz, dan beta 13-30 Hz. Saat membaca EEG, frekuensi harus dikaitkan dengan lokasi kanal, keadaan pasien, dan perubahan pola sepanjang waktu."
  },
  {
    keywords: ["spike", "spike-wave", "spike wave", "sharp wave"],
    answer: "Spike-wave adalah pola puncak tajam yang diikuti gelombang lambat. Spike, spike-and-wave, dan sharp-wave dapat menjadi pola epileptiform, tetapi interpretasinya harus melihat distribusi kanal, durasi, evolusi, dan gejala pasien."
  },
  {
    keywords: ["absence", "absen", "tatapan kosong", "3 hz", "3hz"],
    answer: "Typical absence seizure sering diajarkan dengan episode gangguan kesadaran singkat dan EEG generalized spike-and-wave sekitar 3 Hz. Gejala seperti tatapan kosong mendadak perlu dicocokkan dengan temuan EEG."
  },
  {
    keywords: ["seizure", "kejang", "epilepsi", "epilepsy"],
    answer: "Seizure adalah gangguan sementara aktivitas listrik otak. Pada EEG, seizure dapat terlihat sebagai pola yang berkembang dari waktu ke waktu, misalnya perubahan ritme, amplitudo, frekuensi, atau penyebaran antar kanal."
  },
  {
    keywords: ["interictal", "ictal", "antar kejang"],
    answer: "Ictal berarti saat seizure sedang terjadi. Interictal berarti periode antar seizure. Interictal epileptiform discharges seperti spike atau sharp-wave dapat mendukung kecurigaan epilepsi, tetapi tetap perlu konteks klinis."
  },
  {
    keywords: ["kanal", "channel", "elektroda", "montage"],
    answer: "Kanal EEG membaca beda potensial antara dua elektroda. Contohnya FP1-F7 menggambarkan area frontal-temporal kiri, P7-O1 lebih posterior kiri, dan FZ-CZ berada di garis tengah frontal-sentral."
  },
  {
    keywords: ["fp1", "f7", "t7", "p7", "o1", "fp2", "f8", "t8", "p8", "o2", "fz", "cz", "pz"],
    answer: "Label seperti FP1-F7 atau T7-P7 menunjukkan pasangan elektroda. Huruf F berkaitan dengan frontal, T temporal, P parietal, O oksipital, Z garis tengah, angka ganjil sisi kiri, dan angka genap sisi kanan."
  },
  {
    keywords: ["artefak", "artifact", "gerakan", "kedipan", "blink", "mata", "otot"],
    answer: "Artefak adalah sinyal non-otak yang ikut terekam, misalnya kedipan mata, gerakan kepala, aktivitas otot, jantung, atau gangguan listrik. Artefak sering tidak konsisten antar kanal dan perlu dibedakan dari pola seizure."
  },
  {
    keywords: ["beda artefak dan seizure", "artefak dan seizure", "artefak vs seizure"],
    answer: "Artefak biasanya terkait gerakan atau sumber non-serebral, sering muncul tiba-tiba tanpa evolusi ritmis yang jelas. Seizure lebih dicurigai bila ada pola yang berkembang, memiliki distribusi kanal masuk akal, dan cocok dengan gejala klinis."
  },
  {
    keywords: ["chb-mit", "chb mit", "physionet", "dataset"],
    answer: "CHB-MIT Scalp EEG Database adalah dataset PhysioNet berisi rekaman EEG pediatrik dari pasien dengan seizure intraktabel. Dataset ini memiliki file EDF dan anotasi awal/akhir seizure, sehingga cocok untuk pembelajaran analisis EEG."
  },
  {
    keywords: ["edf", "anotasi", "annotation", "seizure file"],
    answer: "EDF adalah format rekaman sinyal fisiologis yang sering dipakai untuk EEG. Pada CHB-MIT, file dengan seizure disertai anotasi yang menunjukkan waktu awal dan akhir seizure dalam rekaman."
  },
  {
    keywords: ["xai", "explainable", "ai explainer", "segmen penting", "probabilitas"],
    answer: "Explainable AI membantu menjelaskan mengapa model memberi hasil tertentu. Di NeuroCase, kotak segmen penting menandai bagian sinyal yang dianggap berpengaruh, sedangkan probabilitas menunjukkan tingkat keyakinan model pada pola kejang."
  },
  {
    keywords: ["probabilitas pola kejang", "probabilitas berubah", "kenapa probabilitas"],
    answer: "Probabilitas berubah karena kasus yang dianalisis berbeda. Nilai tinggi berarti pola lebih mirip seizure menurut simulasi model; nilai sedang/rendah berarti model melihat bukti yang lebih lemah atau kemungkinan artefak."
  },
  {
    keywords: ["eeg laboratory", "laboratory", "lab", "cara memakai lab", "cara pakai lab"],
    answer: "Di EEG Laboratory, pilih kasus, ubah kanal untuk melihat perbedaan sinyal antar area, lalu geser segmen untuk mengeksplorasi waktu rekaman. Kanal aktif ditandai lebih tebal agar mudah dibandingkan dengan kanal sekitar."
  },
  {
    keywords: ["learning center", "materi", "belajar"],
    answer: "Learning Center berisi materi dasar EEG: pengenalan EEG, gelombang otak, elektroda/kanal, dan gangguan neurologis. Gunakan bagian ini sebelum masuk ke laboratorium atau challenge."
  },
  {
    keywords: ["diagnosis challenge", "challenge", "skor challenge"],
    answer: "Diagnosis Challenge melatih clinical reasoning. Kamu membaca deskripsi kasus dan memilih interpretasi yang paling tepat, lalu sistem memberi feedback berbasis sumber pembelajaran."
  },
  {
    keywords: ["assessment", "pre-test", "post-test", "evaluasi kasus", "kompetensi"],
    answer: "Assessment Center mengukur pemahaman melalui pre-test, post-test, dan evaluasi kasus. Kompetensi mulai dari 0% dan naik berdasarkan skor benar, bukan hanya karena membuka modul."
  },
  {
    keywords: ["clinical reasoning", "reasoning"],
    answer: "Clinical reasoning berarti menggabungkan data klinis, pola EEG, lokasi kanal, durasi, evolusi sinyal, dan kemungkinan artefak untuk membuat interpretasi yang masuk akal."
  },
  {
    keywords: ["normal", "baseline"],
    answer: "Baseline normal adalah aktivitas EEG dasar tanpa pola mencurigakan yang jelas. Namun 'normal' harus dinilai berdasarkan usia, kondisi sadar, montage, dan konteks pasien."
  },
  {
    keywords: ["tidur", "sleep", "k-complex", "spindle"],
    answer: "Tidur mengubah pola EEG. K-complex dan sleep spindle sering terkait tidur N2, sedangkan gelombang lambat lebih dominan pada tidur dalam. Karena itu status sadar pasien penting saat membaca EEG."
  },
  {
    keywords: ["sumber", "referensi", "rujukan"],
    answer: "Konten soal dan penjelasan prototipe ini merujuk pada NCBI Bookshelf/American Epilepsy Society untuk dasar EEG, StatPearls/NCBI untuk gelombang EEG, dan PhysioNet untuk CHB-MIT."
  },
  {
    keywords: ["bukan diagnosis", "validasi klinis", "dokter"],
    answer: "NeuroCase AI adalah media pembelajaran. Hasil simulasi tidak boleh dipakai sebagai diagnosis klinis mandiri dan tetap perlu validasi dosen, klinisi, atau ahli neurologi."
  }
];

function answerTutor(question) {
  const q = question.toLowerCase();
  const match = tutorIntents.find((intent) => intent.keywords.some((keyword) => q.includes(keyword)));
  if (match) {
    return match.answer;
  }
  return "Saya belum punya jawaban spesifik untuk pertanyaan itu. Coba tanyakan tentang EEG dasar, alpha/beta/theta/delta, spike-wave, absence seizure, kanal EEG, artefak, CHB-MIT, XAI, EEG Laboratory, Diagnosis Challenge, atau Assessment Center.";
}

function setupAssessment() {
  $$(".quiz-btn").forEach((button) => {
    button.addEventListener("click", () => {
      renderQuiz(button.dataset.type);
      setView("assessment");
    });
  });
}

function renderQuiz(type) {
  const quiz = quizBank[type];
  if (state.quizScores[type] !== null) {
    $("#quizTitle").textContent = quiz.title;
    $("#quizQuestion").textContent = `Assessment ini sudah selesai. Skor Anda ${state.quizScores[type]}%. Tekan reset progres jika ingin mengulang.`;
    $("#quizSource").textContent = `Sumber: ${quiz.source}`;
    $("#quizOptions").innerHTML = "";
    return;
  }
  state.activeQuizType = type;
  state.activeQuizIndex = 0;
  state.activeQuizCorrect = 0;
  renderQuizQuestion();
}

function renderQuizQuestion() {
  const type = state.activeQuizType;
  const quiz = quizBank[type];
  const question = quiz.questions[state.activeQuizIndex];
  $("#quizTitle").textContent = `${quiz.title} - Soal ${state.activeQuizIndex + 1}/${quiz.questions.length}`;
  $("#quizQuestion").textContent = question.question;
  $("#quizSource").textContent = `Sumber: ${quiz.source}`;
  const list = $("#quizOptions");
  list.innerHTML = "";
  question.options.forEach((option, index) => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = option;
    button.addEventListener("click", () => {
      const correct = index === question.correct;
      if (correct) {
        state.activeQuizCorrect += 1;
      }
      $$("#quizOptions button").forEach((item, optionIndex) => {
        item.disabled = true;
        item.classList.toggle("selected", optionIndex === index);
      });
      $("#quizQuestion").textContent = correct
        ? `Jawaban tepat. ${question.explanation}`
        : `Belum tepat. Jawaban yang disarankan: ${question.options[question.correct]}. ${question.explanation}`;
      setTimeout(nextQuizStep, 950);
    });
    list.appendChild(button);
  });
}

function nextQuizStep() {
  const type = state.activeQuizType;
  const quiz = quizBank[type];
  state.activeQuizIndex += 1;
  if (state.activeQuizIndex < quiz.questions.length) {
    renderQuizQuestion();
    return;
  }
  const score = Math.round((state.activeQuizCorrect / quiz.questions.length) * 100);
  state.quizScores[type] = score;
  updateAssessmentScore(type);
  state.skill = Math.min(100, state.skill + Math.round(score / 10));
  updateSkill();
  $("#quizTitle").textContent = `${quiz.title} selesai`;
  $("#quizQuestion").textContent = `Anda menjawab ${state.activeQuizCorrect} dari ${quiz.questions.length} soal dengan benar. Skor assessment: ${score}%.`;
  $("#quizOptions").innerHTML = "";
}

function updateAssessmentScore(type) {
  const scoreMap = {
    pre: "#preScore",
    post: "#postScore",
    case: "#caseScore"
  };
  $(scoreMap[type]).textContent = `${state.quizScores[type]}%`;
}

function renderExplanation() {
  const item = cases[state.caseIndex];
  $("#confidenceValue").textContent = `${item.confidence}%`;
  $("#confidenceMeter").style.width = `${item.confidence}%`;
  $("#explainText").textContent = item.explain;
  $("#reasonList").innerHTML = "";
  item.reasons.forEach((reason) => {
    const li = document.createElement("li");
    li.textContent = reason;
    $("#reasonList").appendChild(li);
  });
  drawExplain();
}

function updateSkill() {
  $("#skillScore").textContent = `${state.skill}%`;
  $("#skillMeter").style.width = `${state.skill}%`;
}

function setupReset() {
  $("#resetBtn").addEventListener("click", () => {
    state.challengeScore = 0;
    state.answeredChallenges = {};
    state.skill = 0;
    state.activeQuizType = null;
    state.activeQuizIndex = 0;
    state.activeQuizCorrect = 0;
    state.quizScores = {
      pre: null,
      post: null,
      case: null
    };
    $("#challengeScore").textContent = "0";
    $("#preScore").textContent = "0%";
    $("#postScore").textContent = "0%";
    $("#caseScore").textContent = "0%";
    $("#quizTitle").textContent = "Pilih assessment";
    $("#quizQuestion").textContent = "Assessment akan muncul di sini.";
    $("#quizOptions").innerHTML = "";
    $("#quizSource").textContent = "Soal assessment menggunakan rujukan NCBI Bookshelf, American Epilepsy Society, dan PhysioNet.";
    updateSkill();
  });
  $("#runExplainBtn").addEventListener("click", () => {
    state.caseIndex = (state.caseIndex + 1) % cases.length;
    renderCase();
  });
}

function drawGrid(ctx, width, height) {
  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = "#fbfdff";
  ctx.fillRect(0, 0, width, height);
  ctx.strokeStyle = "#e8eef3";
  ctx.lineWidth = 1;
  for (let x = 0; x < width; x += 38) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x, height);
    ctx.stroke();
  }
  for (let y = 0; y < height; y += 34) {
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(width, y);
    ctx.stroke();
  }
}

function channelProfile(channelName) {
  const index = channels.indexOf(channelName);
  const temporal = channelName.includes("T7") || channelName.includes("T8");
  const posterior = channelName.includes("O1") || channelName.includes("O2") || channelName.includes("P7") || channelName.includes("P8") || channelName.includes("PZ");
  const frontal = channelName.includes("FP") || channelName.includes("F7") || channelName.includes("F8") || channelName.includes("FZ");
  return {
    index: Math.max(index, 0),
    amp: temporal ? 1.35 : posterior ? 0.92 : frontal ? 1.12 : 1,
    freq: temporal ? 0.036 : posterior ? 0.024 : frontal ? 0.047 : 0.031,
    noise: frontal ? 9 : posterior ? 4 : temporal ? 7 : 5,
    phase: Math.max(index, 0) * 0.73
  };
}

function visibleChannels(count) {
  const selectedIndex = Math.max(channels.indexOf(state.channel), 0);
  const start = Math.max(0, Math.min(channels.length - count, selectedIndex - Math.floor(count / 2)));
  return channels.slice(start, start + count);
}

function signalValue(x, row, intensity = 1, channelName = state.channel) {
  const profile = channelProfile(channelName);
  const base = Math.sin(x * profile.freq + profile.phase + row) * 18 * profile.amp;
  const rhythm = Math.sin(x * (profile.freq * 2.7) + row * 1.6 + profile.phase) * profile.noise;
  const slowDrift = Math.sin(x * 0.006 + profile.phase) * (profile.amp * 8);
  const window = cases[state.caseIndex].explainWindow;
  const spikeZone = x > window.start && x < window.start + window.width && (state.caseIndex === 0 || state.caseIndex === 2);
  const artifactZone = x > window.start && x < window.start + window.width && state.caseIndex === 1;
  const channelBoost = channelName === state.channel ? 1.35 : 0.65;
  const spike = spikeZone ? Math.sin(x * 0.42 + profile.phase) * 34 * intensity * channelBoost + Math.sin(x * 0.9) * 11 : 0;
  const artifact = artifactZone ? Math.sin(x * 0.18) * 28 * channelBoost + Math.cos(x * 0.04) * 16 : 0;
  return base + rhythm + slowDrift + spike + artifact;
}

function drawSignal(canvas, options = {}) {
  const ctx = canvas.getContext("2d");
  const width = canvas.width;
  const height = canvas.height;
  drawGrid(ctx, width, height);
  const rows = options.rows || 5;
  const color = options.color || "#087f8c";
  const channelList = options.channelList || visibleChannels(rows);
  const selectedRow = Math.max(channelList.indexOf(state.channel), Math.floor(rows / 2));
  for (let row = 0; row < rows; row += 1) {
    const channelName = channelList[row] || channels[row] || state.channel;
    const baseline = ((row + 1) * height) / (rows + 1);
    const selected = channelName === state.channel || row === selectedRow;
    ctx.strokeStyle = selected ? color : "#40566b";
    ctx.lineWidth = selected ? 2.6 : 1.25;
    ctx.beginPath();
    for (let x = 0; x < width; x += 2) {
      const value = signalValue(x + state.segment * 6, row, options.intensity || 1, channelName);
      const y = baseline + value;
      if (x === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
    ctx.stroke();
    ctx.fillStyle = selected ? color : "#667085";
    ctx.font = selected ? "bold 13px Segoe UI" : "13px Segoe UI";
    ctx.fillText(`${channelName}${selected ? "  kanal aktif" : ""}`, 14, baseline - 23);
  }
}

function drawHero(time = 0) {
  const canvas = $("#heroCanvas");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");
  const width = canvas.width;
  const height = canvas.height;
  const gradient = ctx.createLinearGradient(0, 0, width, height);
  gradient.addColorStop(0, "#163247");
  gradient.addColorStop(0.52, "#0b6f7b");
  gradient.addColorStop(1, "#f0f7f8");
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, width, height);

  ctx.globalAlpha = 0.15;
  ctx.strokeStyle = "#ffffff";
  for (let x = 30; x < width; x += 52) {
    ctx.beginPath();
    ctx.moveTo(x, 0);
    ctx.lineTo(x + 90, height);
    ctx.stroke();
  }
  ctx.globalAlpha = 1;

  for (let row = 0; row < 8; row += 1) {
    const baseline = 72 + row * 42;
    ctx.beginPath();
    ctx.strokeStyle = row === 3 ? "#ffcf7a" : "rgba(255,255,255,0.84)";
    ctx.lineWidth = row === 3 ? 3 : 1.5;
    for (let x = 0; x < width; x += 3) {
      const wave = Math.sin((x + time) * 0.03 + row) * 15 + Math.sin((x + time) * 0.11) * 4;
      const spike = x > 350 && x < 470 ? Math.sin((x + time) * 0.5) * 24 : 0;
      const y = baseline + wave + spike;
      if (x === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
    ctx.stroke();
  }
}

function drawBrain() {
  const canvas = $("#brainCanvas");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");
  const width = canvas.width;
  const height = canvas.height;
  const wave = waves[state.wave];
  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = "#f5f8fb";
  ctx.fillRect(0, 0, width, height);

  ctx.fillStyle = "#ffffff";
  ctx.strokeStyle = "#d9e2ea";
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.ellipse(width / 2, height / 2, 165, 125, 0, 0, Math.PI * 2);
  ctx.fill();
  ctx.stroke();

  const nodes = [
    [260, 150], [335, 126], [405, 158], [238, 222], [320, 214], [412, 230], [286, 292], [374, 290]
  ];
  ctx.strokeStyle = "#c7d4dd";
  nodes.forEach((a, index) => {
    nodes.slice(index + 1).forEach((b) => {
      if (Math.hypot(a[0] - b[0], a[1] - b[1]) < 120) {
        ctx.beginPath();
        ctx.moveTo(a[0], a[1]);
        ctx.lineTo(b[0], b[1]);
        ctx.stroke();
      }
    });
  });
  nodes.forEach((node, index) => {
    ctx.beginPath();
    ctx.fillStyle = index % 2 === 0 ? wave.color : "#24364b";
    ctx.arc(node[0], node[1], 10 + Math.sin(index + wave.hz) * 2, 0, Math.PI * 2);
    ctx.fill();
  });

  ctx.strokeStyle = wave.color;
  ctx.lineWidth = 3;
  ctx.beginPath();
  for (let x = 90; x < width - 90; x += 4) {
    const y = 360 + Math.sin(x * 0.03 * (wave.hz / 4)) * 18;
    if (x === 90) ctx.moveTo(x, y);
    else ctx.lineTo(x, y);
  }
  ctx.stroke();
}

function drawLab() {
  drawSignal($("#labCanvas"), { rows: 6, color: "#e45858", intensity: 1.1, channelList: visibleChannels(6) });
}

function drawExplain() {
  const canvas = $("#explainCanvas");
  if (!canvas) return;
  const item = cases[state.caseIndex];
  drawSignal(canvas, { rows: 4, color: "#e45858", intensity: 1.4, channelList: visibleChannels(4) });
  const ctx = canvas.getContext("2d");
  const x = item.explainWindow.start;
  const width = item.explainWindow.width;
  ctx.fillStyle = "rgba(228, 88, 88, 0.16)";
  ctx.fillRect(x, 45, width, canvas.height - 90);
  ctx.strokeStyle = "#e45858";
  ctx.setLineDash([8, 6]);
  ctx.strokeRect(x, 45, width, canvas.height - 90);
  ctx.setLineDash([]);
  ctx.fillStyle = "#17202a";
  ctx.font = "bold 14px Segoe UI";
  ctx.fillText(item.explainWindow.label, x + 12, 34);
}

function drawAll() {
  drawBrain();
  drawLab();
  drawExplain();
}

function animateHero(timestamp) {
  drawHero(timestamp / 16);
  $("#heroStatus").textContent = Math.floor(timestamp / 1400) % 2 === 0 ? "normal baseline" : "spike-wave detected";
  requestAnimationFrame(animateHero);
}

function init() {
  bindNavigation();
  setupLearning();
  setupLab();
  setupChallenge();
  setupTutor();
  setupAssessment();
  setupReset();
  updateSkill();
  renderExplanation();
  drawAll();
  requestAnimationFrame(animateHero);
}

document.addEventListener("DOMContentLoaded", init);
