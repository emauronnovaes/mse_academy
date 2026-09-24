// ---------- Imagens embutidas (base64) ----------
// Embutir garante que as imagens sempre apareçam, mesmo sem uma pasta "images/" junto.
// Para trocar por uma foto real, gere o base64 (ex: no terminal "base64 -w 0 foto.jpg")
// e substitua o valor correspondente abaixo.
const MASCOT_STANDING = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACwCAYAAACIGWW2AAACcUlEQVR4nO3dMS5FQRiA0ft4mxAKSqFSW4JVsAFLsQFWYQlqhRAlBdHTYw/mJV9kzunfm7k3X6b6M3e1xO4Ojn/qPczs9OVxVa6/VS4OAiQlQFICJCVAUgIkJUBSAiQlQFICJCVAUgIkJUBSAiQlQFLDs2Dm+eY2Ok/oBCQlQFICJCVAUgIkJUBSAiQlQFICJCVAUgIkJUBSAiQlQFICJLUe/YOHr89N7INJOQFJCZCUAEkJkJQASQmQlABJCZCUAEkJkJQASQmQlABJCZCUAEml34pdlmX5ub9xv2BodXLue8HMS4CkBEhKgKQESEqApARISoCkBEhKgKQESEqApARISoCkBEhq+H7Aep5v9+xq6Pfvt5cb2cdf/ff9j3ICkhIgKQGSEiApAZISICkBkhIgKQGSEiApAZISICkBkhIgKQGScj/g5NwPyNQESEqApARISoCkBEhKgKQESEqApARISoCkBEhKgKQESEqApIZnwXb2jszzTezj7WmoIScgKQGSEiApAZISICkBkhIgKQGSEiApAZISICkBkhIgKQGSEiCp9eg83/P164a28jeHF/vp+rXR91+/PycgKQGSEiApAZISICkBkhIgKQGSEiApAZISICkBkhIgKQGSEiAp9wMyxP2A/GsCJCVAUgIkJUBSAiQlQFICJCVAUgIkJUBSAiQlQFICJCVAUsPzgKPqecLRebZRsz+/E5CUAEkJkJQASQmQlABJCZCUAEkJkJQASQmQlABJCZCUAEkJkNS63sDqe7veQmr253cCkhIgKQGSEiApAZISICkBkhIgKQGSEiApAZISICkBkhIgKQGS+gV97zbmK/SQfQAAAABJRU5ErkJggg==";
const MASCOT_JUMPING = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACwCAYAAACIGWW2AAACWklEQVR4nO3cMS6EQRiAYSt7CUtBKUShdgSn4AKO4gKcwhHUCiFKClbPAdYdzCZv/vzP0+/O7Oybqb7MYif2eHS6qfcwZxfvL4ty/d1ycRAgKQGSEiApAZISICkBkhIgKQGSEiApAZISICkBkhIgKQGSGp4FM883b6PzhG5AUgIkJUBSAiQlQFICJCVAUgIkJUBSAiQlQFICJCVAUgIkJUBSy9EveP792cY+mCk3ICkBkhIgKQGSEiApAZISICkBkhIgKQGSEiApAZISICkBkhIgqcXm6X7ofb/F+dXQ+3Cj6zOm/v/cgKQESEqApARISoCkBEhKgKQESEqApARISoCkBEhKgKQESEqApJaj82BTt395O/T5r4ebrezjv0b3X8+DugFJCZCUAEkJkJQASQmQlABJCZCUAEkJkJQASQmQlABJCZCUAEnls4DeB2zV86BuQFICJCVAUgIkJUBSAiQlQFICJCVAUgIkJUBSAiQlQFICJCVAUsOzYHsHJ+b5Zuz789X7gEyXAEkJkJQASQmQlABJCZCUAEkJkJQASQmQlABJCZCUAEkJkNRydJ7v7e5jS1v5n+Prw3T92uj51+fnBiQlQFICJCVAUgIkJUBSAiQlQFICJCVAUgIkJUBSAiQlQFICJDX59wFH36ebuqmfvxuQlABJCZCUAEkJkJQASQmQlABJCZCUAEkJkJQASQmQlABJCZBUPku3Wp2l82zr9XN6BnP//W5AUgIkJUBSAiQlQFICJCVAUgIkJUBSAiQlQFICJCVAUgIkJUAAAAAAAAAAAAAAhvwBNmQ/Tc+YHNIAAAAASUVORK5CYII=";
const IMG_SLIDE_1 = "img/slide-1.jpg";
const IMG_SLIDE_2 = "img/slide-2.jpg";
const IMG_SLIDE_3 = "img/slide-3.jpg";
const IMG_SLIDE_4 = "img/slide-4.jpg";
const IMG_SLIDE_5 = "img/slide-5.jpg";

// ---------- Carrossel do hero ----------
  // As imagens são decoração e continuam fixas; os textos vêm do catálogo.
  const IMAGENS_DO_CARROSSEL = [IMG_SLIDE_1, IMG_SLIDE_2, IMG_SLIDE_3, IMG_SLIDE_4, IMG_SLIDE_5];

  // Slide de abertura: é o que fica no ar no instante entre a página abrir e
  // o catálogo chegar da API. Não anuncia curso nenhum de propósito — antes
  // havia cinco aulas de exemplo escritas aqui, e quem tivesse menos de cinco
  // cursos cadastrados ficava com os que sobravam anunciando aula que não
  // existe. Se o catálogo vier vazio ou a API falhar, este slide é o que
  // continua: não tem como voltar a aparecer nome inventado.
  const slides = [
    {
      img: IMG_SLIDE_1,
      title: 'MSE Academy',
      lead: 'Os treinamentos da MSE em um lugar só.'
    }
  ];

  let currentSlide = 0;
  let autoplayTimer = null;
  let fadeTimer = null;
  const AUTOPLAY_MS = 6000;
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Pausa o vídeo de fundo do banner de boas-vindas pra quem prefere menos
  // movimento na tela (acessibilidade) — fica só a imagem parada do 1º frame.
  const welcomeVideo = document.querySelector('.welcome-video');
  if(welcomeVideo && prefersReducedMotion){
    welcomeVideo.pause();
    welcomeVideo.removeAttribute('autoplay');
  }

  // O vídeo do banner tem dezenas de MB e é decorativo — o degradê por
  // baixo já é o fundo. Baixá-lo junto com a página fazia todo o resto
  // (trilha, cursos, o vídeo da aula) disputar banda com ele desde o
  // primeiro instante. Agora só começa quando o navegador está ocioso,
  // com a tela já pronta pra usar.
  function carregarVideoDoBanner(){
    if(!welcomeVideo || !welcomeVideo.dataset.src) return;
    if(prefersReducedMotion) return; // quem pediu menos movimento não precisa baixar
    welcomeVideo.src = welcomeVideo.dataset.src;
    delete welcomeVideo.dataset.src;
    welcomeVideo.load();
    // O atributo autoplay só vale pro src que existia quando a página
    // carregou. Como o src passou a ser definido depois, o play tem que
    // ser pedido na mão — senão o vídeo ficaria parado no primeiro frame.
    // Fica mudo, então o navegador permite; o catch cobre o caso de
    // alguma política bloquear mesmo assim.
    welcomeVideo.play().catch(() => {});
  }
  if(window.requestIdleCallback){
    requestIdleCallback(carregarVideoDoBanner, { timeout: 4000 });
  } else {
    window.addEventListener('load', () => setTimeout(carregarVideoDoBanner, 1200));
  }

  const slideTitle = document.getElementById('slideTitle');
  const slideLead = document.getElementById('slideLead');
  const slideImage = document.getElementById('slideImage');
  const slideCurrent = document.getElementById('slideCurrent');
  const slideTotal = document.getElementById('slideTotal');
  const dotsWrap = document.getElementById('slideDots');
  const heroSection = document.querySelector('.hero');

  // As bolinhas são redesenhadas quando o catálogo chega: a quantidade de
  // slides passa a ser a de cursos que existem, então não dá pra montá-las
  // uma vez só no começo.
  function montarBolinhas(){
    dotsWrap.innerHTML = '';
    slideTotal.textContent = slides.length;
    slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.setAttribute('role', 'tab');
      dot.setAttribute('aria-label', `Ir para o slide ${i + 1}`);
      dot.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
      if (i === 0) dot.classList.add('active');
      dot.addEventListener('click', () => goToSlide(i));
      dotsWrap.appendChild(dot);
    });
    // Uma bolinha sozinha não navega pra lugar nenhum.
    dotsWrap.hidden = slides.length < 2;
  }

  montarBolinhas();

  function renderSlide(index){
    const s = slides[index];

    clearTimeout(fadeTimer);
    slideImage.classList.add('fading');
    slideTitle.classList.add('hero-text-fading');
    slideLead.classList.add('hero-text-fading');

    fadeTimer = setTimeout(() => {
      slideImage.src = s.img;
      slideTitle.textContent = s.title;
      slideLead.innerHTML = s.lead;
      slideCurrent.textContent = index + 1;

      slideImage.classList.remove('fading');
      slideTitle.classList.remove('hero-text-fading');
      slideLead.classList.remove('hero-text-fading');
    }, prefersReducedMotion ? 0 : 220);

    dotsWrap.querySelectorAll('button').forEach((d, i) => {
      d.classList.toggle('active', i === index);
      d.setAttribute('aria-selected', String(i === index));
    });
  }

  function renderSlideInstant(index){
    const s = slides[index];
    slideImage.src = s.img;
    slideTitle.textContent = s.title;
    slideLead.innerHTML = s.lead;
    slideCurrent.textContent = index + 1;
    dotsWrap.querySelectorAll('button').forEach((d, i) => {
      d.classList.toggle('active', i === index);
      d.setAttribute('aria-selected', String(i === index));
    });
  }

  function goToSlide(index){
    const next = (index + slides.length) % slides.length;
    if(next === currentSlide){
      restartAutoplay();
      return;
    }
    currentSlide = next;
    renderSlide(currentSlide);
    restartAutoplay();
  }

  function nextSlide(){ goToSlide(currentSlide + 1); }
  function prevSlide(){ goToSlide(currentSlide - 1); }

  document.getElementById('nextSlide').addEventListener('click', nextSlide);
  document.getElementById('prevSlide').addEventListener('click', prevSlide);

  function startAutoplay(){
    if(prefersReducedMotion) return;
    autoplayTimer = setInterval(nextSlide, AUTOPLAY_MS);
  }
  function stopAutoplay(){ clearInterval(autoplayTimer); }
  function restartAutoplay(){ stopAutoplay(); startAutoplay(); }

  heroSection.addEventListener('mouseenter', stopAutoplay);
  heroSection.addEventListener('mouseleave', restartAutoplay);
  heroSection.addEventListener('focusin', stopAutoplay);
  heroSection.addEventListener('focusout', restartAutoplay);

  heroSection.addEventListener('keydown', (e) => {
    const isTypingField = e.target.matches('input, textarea, select, [contenteditable="true"]');
    if(isTypingField) return; // não rouba as setas de quem está digitando/editando texto
    if(e.key === 'ArrowRight') nextSlide();
    if(e.key === 'ArrowLeft') prevSlide();
  });

  // Pausa o autoplay quando a aba fica em segundo plano, evitando "pulos" de slide ao voltar
  document.addEventListener('visibilitychange', () => {
    if(document.hidden) stopAutoplay();
    else startAutoplay();
  });

  renderSlideInstant(0);
  startAutoplay();

// ---------- Persistência de visita (troque por dado real da sessão do portal) ----------
  function getStorage(){
    try{
      localStorage.setItem('__mse_test__','1');
      localStorage.removeItem('__mse_test__');
      return localStorage;
    }catch(e){
      // Ambiente sem acesso a localStorage (ex.: pré-visualização isolada) — usa memória da aba
      window.__mseMemoryStore = window.__mseMemoryStore || {};
      return {
        getItem:(k)=> window.__mseMemoryStore[k] ?? null,
        setItem:(k,v)=>{ window.__mseMemoryStore[k]=v; }
      };
    }
  }
  const store = getStorage();
  const hasVisited = store.getItem('mse_academy_visited') === '1';

  // ---------- Estatísticas reais da home (colaboradores, tutoriais, áreas) ----------
  // Busca do backend (api/stats.php, endpoint público) em vez de usar
  // números fixos — antes disso, os 3 valores começam em 0 no HTML.
  (async function carregarEstatisticasReais(){
    try{
      const res = await fetch('api/stats.php');
      const data = await res.json();
      const mapa = {
        statColaboradores: data.colaboradores_atendidos,
        statTutoriais: data.tutoriais_disponiveis,
        statAreas: data.areas_cobertas,
      };
      Object.keys(mapa).forEach(id => {
        const el = document.getElementById(id);
        if(el && typeof mapa[id] === 'number') el.dataset.count = String(mapa[id]);
      });
    }catch(e){
      console.warn('Não consegui carregar as estatísticas reais:', e.message);
      // Sem dado real disponível, os contadores ficam em 0 — melhor
      // mostrar 0 de verdade do que inventar um número.
    }
  })();

  // ---------- Banner de boas-vindas (só aparece na aba Integração) ----------
  // O nome real vem da sessão de login de verdade (SSO ou quick_login,
  // ver initRealAdminIntegration no final do arquivo) — guardado em
  // localStorage assim que o login termina. Enquanto isso não roda
  // ainda (ou se falhar), cai no "Colaborador" de sempre.
  function updateGreetingBanner(realName){
    const userName = realName || localStorage.getItem('mse_academy_real_user_name') || new URLSearchParams(location.search).get('nome') || 'Colaborador';

    const hour = new Date().getHours();
    const greeting = hour < 12 ? 'Bom dia' : hour < 18 ? 'Boa tarde' : 'Boa noite';
    document.getElementById('greetingTime').textContent = greeting;

    if(hasVisited){
      document.getElementById('greetingName').innerHTML = `Bem-vindo de volta, <span>${userName}</span>.`;
      document.getElementById('welcomeSub').textContent = 'Continue de onde parou ou procure um novo tutorial no Portal MSE.';
    } else {
      document.getElementById('greetingName').innerHTML = `Bem-vindo, <span>${userName}</span>.`;
      document.getElementById('welcomeSub').textContent = 'Vamos começar pela sua integração ao Portal MSE.';
    }
  }
  updateGreetingBanner(); // mostra algo já de cara (Colaborador ou nome salvo de uma visita anterior)

  // ---------- Abas: Integração / Cursos ----------
  function showView(name){
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.getElementById('view-' + name).classList.add('active');
    document.querySelectorAll('#mainTabs button').forEach(b => {
      const isActive = b.dataset.view === name;
      b.classList.toggle('active', isActive);
      b.setAttribute('aria-selected', String(isActive));
    });
    // A barra de XP (com os raios) fica dentro da aba Cursos. Se ela for
    // medida enquanto a aba ainda está escondida (display:none), o canvas
    // trava em tamanho 0x0 pra sempre. Recalcula toda vez que a aba abre.
    if(name === 'cursos'){
      requestAnimationFrame(() => resizeLightningCanvas());
    }
  }

  document.getElementById('mainTabs').addEventListener('click', (e) => {
    const btn = e.target.closest('button');
    if(!btn) return;
    showView(btn.dataset.view);
  });

  // A pedido: sempre abre na aba Integração, mesmo em visitas seguintes
  // (a saudação "bem-vindo de volta" acima continua mudando normalmente).
  showView('integracao');

  // A partir daqui, a visita já conta como "feita" para a próxima vez que a página carregar
  store.setItem('mse_academy_visited', '1');

  // ================================================================
  // ---------- Trilha obrigatória de vídeos (onboarding) ----------
  // ================================================================
  // Cada módulo tem um vídeo do YouTube que precisa ser assistido até
  // o fim (>=95%) para liberar a pergunta. Responder corretamente libera
  // o próximo módulo. Tudo é sequencial e o progresso fica salvo.
  //
  // TROQUE "youtubeId" pelo ID real de cada vídeo (é o trecho depois de
  // "v=" na URL do YouTube, ex: https://youtube.com/watch?v=ABC123 -> "ABC123").
  // ================================================================
  // ---------- Conteúdo vindo do banco ----------
  // ================================================================
  // Antes as aulas eram listas fixas neste arquivo, sem ligação nenhuma
  // com o que o admin cadastrava: vídeo enviado pelo painel nunca
  // aparecia, e o relatório "Quem assistiu" ficava sempre zerado porque
  // o progresso só existia no localStorage de cada navegador.
  // Enquanto ninguém abriu a aula, só existe o arredondamento que o
  // admin digitou ("3 min"). Depois da primeira abertura passa a mostrar
  // o tempo exato do vídeo.
  function formatarDuracao(segundos, minutosFallback){
    if(!segundos || segundos < 1) return (minutosFallback || 0) + ' min';
    const m = Math.floor(segundos / 60);
    const s = Math.round(segundos % 60);
    if(m === 0) return s + ' s';
    return `${m} min ${String(s).padStart(2, '0')} s`;
  }

  // O servidor não tem como descobrir a duração sozinho (precisaria
  // baixar o vídeo do S3 ou consultar a API do YouTube), mas o player já
  // sabe assim que carrega. Só a primeira gravação vale.
  async function registrarDuracao(courseId, segundos){
    if(!segundos || !isFinite(segundos) || segundos < 1) return;
    const aula = ONBOARDING.concat(courses).find(c => c.id === courseId);
    if(aula && aula.duracaoSegundos) return; // já conhecida
    try {
      await apiPost('api/courses/duracao.php', { course_id: courseId, segundos: Math.round(segundos) });
      if(aula) aula.duracaoSegundos = Math.round(segundos);
    } catch(e){
      console.warn('[duracao] não consegui registrar:', e.message);
    }
  }
  let conteudoCarregado = false;
  const detalheCache = new Map();

  function tokenSessao(){
    try { return localStorage.getItem('mse_academy_real_session_token'); }
    catch(e){ return null; }
  }

  async function apiGet(caminho){
    const token = tokenSessao();
    const res = await fetch(caminho, {
      credentials: 'include',
      headers: token ? { 'Authorization': 'Bearer ' + token } : {},
    });
    const dados = await res.json().catch(() => ({}));
    if(!res.ok) throw new Error(dados.error || ('Erro ' + res.status));
    return dados;
  }

  async function apiPost(caminho, corpo){
    const token = tokenSessao();
    const res = await fetch(caminho, {
      method: 'POST',
      credentials: 'include',
      headers: Object.assign({ 'Content-Type': 'application/json' }, token ? { 'Authorization': 'Bearer ' + token } : {}),
      body: JSON.stringify(corpo),
    });
    const dados = await res.json().catch(() => ({}));
    if(!res.ok) throw new Error(dados.error || ('Erro ' + res.status));
    return dados;
  }

  // O vídeo vem como URL assinada que expira em 30 min, então o detalhe
  // é buscado na hora de abrir o módulo — não dá pra pré-carregar tudo.
  async function carregarDetalheCurso(id){
    if(detalheCache.has(id)) return detalheCache.get(id);
    const dados = await apiGet('api/courses/detail.php?id=' + encodeURIComponent(id));
    detalheCache.set(id, dados.course);
    return dados.course;
  }

  // Carregada do banco em carregarConteudo(), logo apos o login.
  // Antes era uma lista fixa aqui, que nao tinha relacao nenhuma com
  // o que o admin cadastrava — video enviado pelo painel nunca
  // aparecia pra ninguem.
  let ONBOARDING = [];

  const ONB_STORAGE_KEY = 'mse_academy_onboarding_v1';
  const ONB_PASS_THRESHOLD = 0.95; // 95% do vídeo assistido libera a pergunta
  const ONB_QUIZ_PASS_RATIO = 0.75; // precisa acertar 75% das perguntas (3 de 4) de primeira pra liberar o baú

  function loadOnboardingProgress(){
    try{
      const raw = store.getItem(ONB_STORAGE_KEY);
      const parsed = raw ? JSON.parse(raw) : { completed: [] };
      if(!parsed.firstTry) parsed.firstTry = {}; // { [moduleId]: true/false, acertou de primeira ou não }
      return parsed;
    }catch(e){
      return { completed: [], firstTry: {} };
    }
  }
  function saveOnboardingProgress(progress){
    store.setItem(ONB_STORAGE_KEY, JSON.stringify(progress));
  }

  // Quantas perguntas foram respondidas certo já na primeira tentativa,
  // do total de módulos concluídos até agora — isso é o que decide se o
  // baú libera ou não (precisa de 75%, ver ONB_QUIZ_PASS_RATIO).
  function getOnbQuizAccuracy(){
    const total = ONBOARDING.length;
    const correct = ONBOARDING.filter(m => onbProgress.firstTry[m.id] === true).length;
    return { correct, total, pct: total ? Math.round((correct / total) * 100) : 0 };
  }
  // Trilha sem nenhuma pergunta cadastrada: assistir tudo é o suficiente
  // pra abrir o baú. Sem isso a exigência de 75% de acerto nunca seria
  // atingida (0 acertos de 0 perguntas) e a recompensa ficaria travada
  // pra sempre.
  function trilhaTemPerguntas(){
    // temQuiz vem da listagem: as perguntas em si só chegam ao abrir o
    // módulo, então olhar mod.questions aqui diria "sem perguntas" mesmo
    // quando existem.
    return ONBOARDING.some(m => m.temQuiz);
  }
  function isOnbQuizPassed(){
    if(!trilhaTemPerguntas()) return true;
    return getOnbQuizAccuracy().correct / ONBOARDING.length >= ONB_QUIZ_PASS_RATIO;
  }

  let onbProgress = loadOnboardingProgress();
  let onbPlayer = null;
  let onbMaxWatchedPct = 0;
  let onbVideoUnlocked = false; // true quando >=95% assistido (libera a pergunta)
  let onbYtTimer = null; // consulta a posição do player do YouTube (ele não avisa sozinho)

  function onbCurrentIndex(){
    // Primeiro módulo OBRIGATÓRIO ainda não concluído. Aula opcional
    // (playlist, extras) não pode segurar a trilha — senão bastaria
    // ignorar uma pra nunca chegar ao fim.
    const idx = ONBOARDING.findIndex(m => m.obrigatorio !== false && !onbProgress.completed.includes(m.id));
    return idx === -1 ? ONBOARDING.length : idx;
  }

  // ---------- Carrega a API do YouTube sob demanda (só quando a trilha é aberta) ----------
  let ytApiPromise = null;
  function loadYouTubeApi(){
    if(window.YT && window.YT.Player) return Promise.resolve();
    if(ytApiPromise) return ytApiPromise;
    ytApiPromise = new Promise((resolve) => {
      window.onYouTubeIframeAPIReady = resolve;
      const tag = document.createElement('script');
      tag.src = 'https://www.youtube.com/iframe_api';
      document.head.appendChild(tag);
    });
    return ytApiPromise;
  }

  function updateOnboardingHeader(){
    // A barra conta só as aulas obrigatórias. Incluir as opcionais fazia
    // o número parecer pior do que é: quem assistiu tudo que precisava
    // via "4 de 6" e achava que faltava coisa.
    const obrigatorias = ONBOARDING.filter(m => m.obrigatorio !== false);
    const total = obrigatorias.length;
    const done = obrigatorias.filter(m => onbProgress.completed.includes(m.id)).length;
    const pct = total ? Math.round((done / total) * 100) : 0;
    document.getElementById('onboardingProgressLabel').textContent = `${done} de ${total} módulos concluídos`;
    document.getElementById('onboardingProgressPct').textContent = `${pct}%`;
    document.getElementById('onboardingProgressFill').style.width = pct + '%';

    // Percentual de acerto nas perguntas (precisa de 75% pra abrir o baú).
    // Só aparece depois que a pessoa já respondeu pelo menos 1 pergunta —
    // antes disso não tem nada ainda pra mostrar.
    const accuracyEl = document.getElementById('onboardingQuizAccuracy');
    const answeredCount = Object.keys(onbProgress.firstTry).length;
    if(accuracyEl){
      if(answeredCount === 0){
        accuracyEl.textContent = '';
      } else {
        const quiz = getOnbQuizAccuracy();
        const passed = isOnbQuizPassed();
        accuracyEl.innerHTML = `<i class="fa-solid ${passed ? 'fa-check' : 'fa-circle-exclamation'}" aria-hidden="true"></i> ${quiz.correct} de ${quiz.total} perguntas certas de primeira (${quiz.pct}%) — precisa de 75% pra abrir o baú`;
        accuracyEl.classList.toggle('is-passed', passed);
        accuracyEl.classList.toggle('is-warn', !passed);
      }
    }
  }

  // As posições eram 5 fixas (4 módulos + baú). Como a trilha passou a
  // vir do banco, a quantidade de módulos varia — com a lista fixa, um
  // módulo a mais deixava `positions[i]` indefinido e a tela quebrava
  // inteira num TypeError. Agora são calculadas pra qualquer quantidade,
  // mantendo o mesmo espaçamento visual de antes (6% no topo, 92% no fim).
  const ONB_INICIO_PCT = 6, ONB_FIM_PCT = 92;
  const ONB_ESPACO_PX = 150;   // distância entre os centros de duas casas
  const ONB_PADDING_PX = 128;  // o padding vertical do viewport (64 em cima + 64 embaixo)

  function onbPathPositions(nodeCount){
    if(nodeCount <= 1) return [{ x: 50, y: 50 }];
    const passo = (ONB_FIM_PCT - ONB_INICIO_PCT) / (nodeCount - 1);
    return Array.from({ length: nodeCount }, (_, i) => ({ x: 50, y: ONB_INICIO_PCT + passo * i }));
  }

  // As posições são percentuais de um contêiner que tinha altura fixa
  // (aspect-ratio 1/2.05): com mais aulas, as casas iam se espremendo
  // uma contra a outra em vez de a trilha crescer. Aqui a altura passa a
  // ser calculada pela quantidade de casas, mantendo a mesma distância
  // entre elas, não importa quantas aulas existam.
  function ajustarAlturaDaTrilha(nodeCount){
    const viewport = document.querySelector('.onb-path-viewport');
    if(!viewport) return;
    if(nodeCount <= 1){
      viewport.style.removeProperty('height');
      viewport.style.removeProperty('aspect-ratio');
      return;
    }
    const faixa = (ONB_FIM_PCT - ONB_INICIO_PCT) / 100; // fração da altura ocupada pelas casas
    const alturaTrilha = (ONB_ESPACO_PX * (nodeCount - 1)) / faixa;
    viewport.style.aspectRatio = 'auto'; // senão a altura calculada é ignorada
    viewport.style.height = Math.round(alturaTrilha + ONB_PADDING_PX) + 'px';
  }
  let onbPawnLastIndex = null;

  function renderOnboarding(){
    // Sem esse guard, a trilha renderiza vazia no carregamento da página
    // (o conteúdo só chega depois do login) e o usuário vê "0 de 0" e a
    // tela do baú antes de qualquer coisa.
    if(!conteudoCarregado) return;
    updateOnboardingHeader();
    renderActiveModuleSection(); // zera o progresso do vídeo do módulo atual...
    renderOnbPath();             // ...antes do anel de progresso ler esse valor
  }

  function openChestAnimation(node){
    const svg = node.querySelector('.chest-svg');
    if(!svg || svg.classList.contains('is-open')) return; // evita clique duplo no meio da animação

    svg.classList.add('is-open');
    // Pausa o "flutuar" ambiente enquanto acontece o momento principal —
    // duas animações concorrentes (flutuação + tampa abrindo) competiam
    // por atenção. O clímax merece o movimento inteiro só pra ele.
    node.classList.add('is-opening');

    // O auge visual da tampa (o estouro além do ponto de descanso) acontece
    // aos 65% dos 1.3s = ~845ms — é aí que a recompensa (luz + partículas)
    // precisa aparecer, não no instante do clique. Antes disso, os efeitos
    // já tinham murchado quando a tampa ainda nem tinha terminado de abrir.
    const PEAK_DELAY = 780;

    setTimeout(() => {
      // Feixes de luz irradiando do centro, como um estouro de brilho
      const beamCount = 8;
      for(let i = 0; i < beamCount; i++){
        const beam = document.createElement('div');
        beam.className = 'chest-light-beam';
        const angle = (i / beamCount) * 360 + (Math.random() * 10 - 5);
        beam.style.transform = `translate(-50%, -100%) rotate(${angle}deg)`;
        beam.style.animationDelay = (Math.random() * 0.08) + 's';
        node.appendChild(beam);
        setTimeout(() => beam.remove(), 1150);
      }

      // Estoura partículas douradas/roxas saindo do centro do baú, em todas as direções
      for(let i = 0; i < 22; i++){
        const particle = document.createElement('div');
        particle.className = 'chest-particle';
        const angle = (i / 22) * Math.PI * 2 + (Math.random() - 0.5) * 0.4;
        const dist = 40 + Math.random() * 44;
        particle.style.setProperty('--px', `${Math.cos(angle) * dist}px`);
        particle.style.setProperty('--py', `${Math.sin(angle) * dist - 24}px`);
        particle.style.background = i % 3 === 0 ? '#8965C4' : '#F9A825';
        const size = 4 + Math.random() * 4;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.animationDelay = (Math.random() * 0.15) + 's';
        node.appendChild(particle);
        setTimeout(() => particle.remove(), 1250);
      }
    }, PEAK_DELAY);

    // Fecha de novo depois de alguns segundos, pra poder abrir de novo se clicar outra vez
    setTimeout(() => {
      svg.classList.remove('is-open');
      node.classList.remove('is-opening');
    }, 2600);
  }

  function renderOnbPath(){
    const track = document.getElementById('onbPathTrack');
    if(!track) return;

    const currentIdx = onbCurrentIndex();
    const allDone = currentIdx >= ONBOARDING.length;
    const NODE_COUNT = ONBOARDING.length + 1; // +1 = a casa do baú
    const positions = onbPathPositions(NODE_COUNT);
    ajustarAlturaDaTrilha(NODE_COUNT);

    let tilesLayer = track.querySelector('.onb-tiles-layer');
    if(!tilesLayer){
      tilesLayer = document.createElement('div');
      tilesLayer.className = 'onb-tiles-layer';
      tilesLayer.style.position = 'absolute';
      tilesLayer.style.inset = '0';
      track.appendChild(tilesLayer);
    }
    // Canvas de raios removido — ficava causando um efeito estranho sem a
    // barra visível junto em alguns casos. Fio simples só com o brilho
    // estático em CSS, mais previsível.
    const oldLightningCanvas = track.querySelector('.onb-lightning-canvas');
    if(oldLightningCanvas) oldLightningCanvas.remove();

    // ---------- Fio vermelho reto conectando as casas ----------
    let cablesSvg = `<svg class="onb-cables-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">`;
    for(let i = 0; i < positions.length - 1; i++){
      const p1 = positions[i];
      const p2 = positions[i + 1];
      const color = '#C4212C';

      const isEnergized = allDone || i < currentIdx;
      const pathD = `M ${p1.x},${p1.y} L ${p2.x},${p2.y}`;

      if(isEnergized){
        cablesSvg += `
          <path d="${pathD}" class="onb-cable onb-cable-glow" style="stroke:${color}"/>
          <path d="${pathD}" class="onb-cable onb-cable-core" style="stroke:${color}"/>
        `;
      } else {
        cablesSvg += `<path d="${pathD}" class="onb-cable onb-cable-off"/>`;
      }
    }
    cablesSvg += `</svg>`;
    tilesLayer.innerHTML = cablesSvg;

    for(let i = 0; i < NODE_COUNT; i++){
      const isChestNode = i === ONBOARDING.length; // a 5ª casa, própria do baú
      const mod = isChestNode ? null : ONBOARDING[i];
      // O baú só libera se TODOS os módulos foram assistidos E a pessoa
      // acertou pelo menos 75% das perguntas (3 de 4) — só assistir tudo
      // não é mais suficiente.
      const isDone = isChestNode ? (allDone && isOnbQuizPassed()) : onbProgress.completed.includes(mod.id);
      const isCurrent = i === currentIdx && !isDone;
      const isLocked = i > currentIdx;
      const pos = positions[i];
      const title = isChestNode ? 'Recompensa da trilha' : mod.title;

      const node = document.createElement('button');
      node.type = 'button';
      node.className = 'onb-node' + (isDone ? ' is-done' : isCurrent ? ' is-current' : ' is-locked')
        + (isChestNode ? ' is-finish' : '');
      node.style.left = pos.x + '%';
      node.style.top = pos.y + '%';
      node.disabled = isLocked;
      node.setAttribute('aria-label', `${title} — ${isDone ? 'concluído' : isCurrent ? 'em andamento' : 'bloqueado'}`);


      // O baú é montado com a tampa num <g> separado da base — assim dá
      // pra girar só a tampa (como uma dobradiça de verdade) quando abre,
      // sem precisar redesenhar o baú inteiro. Os gradientes dão o efeito
      // de volume/3D (luz batendo de cima-esquerda), em vez de cor chapada.
      // O baú é montado com a tampa num <g> separado da base — assim dá
      // pra girar só a tampa (como uma dobradiça de verdade) quando abre,
      // sem precisar redesenhar o baú inteiro. Os gradientes dão o efeito
      // de volume/3D (luz batendo de cima-esquerda), em vez de cor chapada.
      const chestSvg = `
        <svg class="chest-svg" viewBox="0 0 24 24" aria-hidden="true">
          <defs>
            <linearGradient id="chestGold" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="#FFDF7E"/>
              <stop offset="45%" stop-color="#F9A825"/>
              <stop offset="100%" stop-color="#C98700"/>
            </linearGradient>
            <linearGradient id="chestGoldPost" x1="0" y1="0" x2="1" y2="0">
              <stop offset="0%" stop-color="#FFE9A8"/>
              <stop offset="55%" stop-color="#FFC947"/>
              <stop offset="100%" stop-color="#E0961A"/>
            </linearGradient>
            <linearGradient id="chestPurple" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#A98EE0"/>
              <stop offset="55%" stop-color="#8965C4"/>
              <stop offset="100%" stop-color="#6E4FA3"/>
            </linearGradient>
            <linearGradient id="chestLockPlate" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="#FFE9A8"/>
              <stop offset="100%" stop-color="#D99500"/>
            </linearGradient>
          </defs>
          <g class="chest-glow">
            <circle cx="12" cy="13" r="7" fill="#fff8de"/>
          </g>
          <g class="chest-base">
            <rect x="3" y="11" width="18" height="9" rx="2" fill="url(#chestGold)"/>
            <rect x="3" y="11" width="4" height="9" fill="url(#chestGoldPost)"/>
            <rect x="17" y="11" width="4" height="9" fill="url(#chestGoldPost)"/>
            <rect x="7.5" y="13.2" width="9" height="4.6" rx="0.6" fill="url(#chestPurple)"/>
            <rect x="7.5" y="15.2" width="9" height="1" fill="#5A3F8C"/>
            <rect x="8.3" y="10.3" width="7.4" height="5.4" rx="1.2" fill="url(#chestLockPlate)"/>
            <circle cx="10.6" cy="12.3" r="1" fill="#5A3F8C"/>
            <rect x="10.15" y="12.9" width="0.9" height="1.4" fill="#5A3F8C"/>
            <circle cx="13.4" cy="12.3" r="1" fill="#5A3F8C"/>
            <rect x="12.95" y="12.9" width="0.9" height="1.4" fill="#5A3F8C"/>
            <circle cx="5" cy="18" r="0.8" fill="#FFE9A8"/>
            <circle cx="19" cy="18" r="0.8" fill="#FFE9A8"/>
          </g>
          <g class="chest-lid">
            <rect x="3" y="5" width="18" height="7" rx="2" fill="url(#chestGold)"/>
            <rect x="3" y="5" width="4" height="7" fill="url(#chestGoldPost)"/>
            <rect x="17" y="5" width="4" height="7" fill="url(#chestGoldPost)"/>
            <rect x="3.5" y="5.4" width="17" height="1.1" rx="0.5" fill="#FFF3D0" opacity="0.55"/>
            <rect x="7.5" y="6.2" width="9" height="4.6" rx="0.6" fill="url(#chestPurple)"/>
            <rect x="7.5" y="8.2" width="9" height="1" fill="#5A3F8C"/>
            <circle cx="5" cy="7" r="0.8" fill="#FFE9A8"/>
            <circle cx="19" cy="7" r="0.8" fill="#FFE9A8"/>
          </g>
        </svg>
        <div class="chest-drop-shadow"></div>
      `;
      const iconHtml = isChestNode
        ? (chestSvg + (isDone ? '' : '<div class="chest-lock-badge"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>'))
        : isDone
          ? '<i class="fa-solid fa-check" aria-hidden="true"></i>'
          : isLocked
            ? '<i class="fa-solid fa-lock" aria-hidden="true"></i>'
            : String(i + 1);

      const labelSide = pos.x >= 50 ? 'is-label-left' : 'is-label-right';
      node.innerHTML = `${iconHtml}`;

      node.addEventListener('click', () => {
        if(isLocked) return;
        onbSelectedIndex = isChestNode ? 'chest' : i;
        renderActiveModuleSection();
        if(isChestNode && isDone) openChestAnimation(node);
        document.getElementById('onbActiveModule')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
      tilesLayer.appendChild(node);

      // O balão fica FORA do botão de propósito — se ficasse dentro, ele
      // "pulsava"/flutuava junto com a animação da casa (atual ou baú),
      // o que ficava com um efeito estranho de balão tremendo.
      const label = document.createElement('span');
      label.className = `onb-node-label onb-node-label-standalone ${labelSide} ${isDone ? 'is-done-label' : ''} ${isCurrent ? 'is-current-label' : ''}`;
      label.style.left = pos.x + '%';
      label.style.top = pos.y + '%';
      label.textContent = title;
      // Deixa claro que dá pra pular — sem isso a pessoa trava achando
      // que precisa concluir a playlist pra seguir.
      if(!isChestNode && mod.obrigatorio === false){
        const extra = document.createElement('span');
        extra.className = 'onb-node-opcional';
        extra.textContent = 'opcional';
        label.appendChild(extra);
      }

      // Admin renomeia clicando no próprio balão. O balão é
      // pointer-events:none pra não atrapalhar o clique na casa, então a
      // classe abaixo devolve o clique só pra quem pode editar.
      if(!isChestNode && window.mseEhAdmin && typeof window.mseRenomearAula === 'function'){
        label.classList.add('is-editavel');
        label.title = 'Clique para renomear';
        label.addEventListener('click', (e) => {
          e.stopPropagation(); // senão o clique vazaria pra casa da trilha
          window.mseRenomearAula(mod.id, mod.title);
        });
      }

      tilesLayer.appendChild(label);

      // Mesma lógica: a bolha "Continuar" também fica fora do botão, por
      // exatamente o mesmo motivo (senão pulsava junto com a casa atual).
      if(isCurrent){
        const callout = document.createElement('div');
        callout.className = 'onb-callout onb-callout-standalone';
        callout.style.left = pos.x + '%';
        callout.style.top = pos.y + '%';
        callout.innerHTML = 'Continuar<span class="onb-callout-arrow"></span>';
        tilesLayer.appendChild(callout);
      }
    }

    // Mascote removido a pedido — a casa "atual" já pulsa sozinha, isso
    // basta como indicação visual de onde a pessoa está na trilha.
    const oldPawn = track.querySelector('.onb-pawn');
    if(oldPawn) oldPawn.remove();
  }

  let onbSelectedIndex = null; // número (módulo) | 'chest' | null (usa o atual)

  function renderActiveModuleSection(){
    const wrap = document.getElementById('onbActiveModule');
    if(!wrap) return;
    const currentIdx = onbCurrentIndex();
    const allDone = currentIdx >= ONBOARDING.length;

    // Mostra a tela de parabéns quando: clicaram na casa do baú, OU
    // (nada selecionado e a trilha inteira já foi concluída).
    const showingChest = onbSelectedIndex === 'chest' || (onbSelectedIndex === null && allDone);

    if(showingChest){
      const quiz = getOnbQuizAccuracy();
      const passed = isOnbQuizPassed();

      wrap.innerHTML = passed ? `
        <div class="onb-module is-done onb-congrats">
          <div class="onb-congrats-inner">
            <div class="onb-congrats-emoji"><i class="fa-solid fa-medal" aria-hidden="true"></i></div>
            <h4>Parabéns! Você concluiu a integração</h4>
            <p>${trilhaTemPerguntas() ? `Você acertou ${quiz.correct} de ${quiz.total} perguntas (${quiz.pct}%). ` : ''}Agora acesse a aba <strong>Cursos</strong> — o catálogo de mini-aulas também é obrigatório. Clique em qualquer módulo acima pra rever a integração quando precisar.</p>
          </div>
        </div>` : `
        <div class="onb-module onb-congrats">
          <div class="onb-congrats-inner">
            <div class="onb-congrats-emoji onb-congrats-emoji-warn"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></div>
            <h4>Quase lá — falta acertar mais uma pergunta</h4>
            <p>Você assistiu todos os vídeos, mas acertou só ${quiz.correct} de ${quiz.total} perguntas (${quiz.pct}%). Pra abrir o baú, precisa de pelo menos 75% (3 de 4). Clique em um módulo acima, assista de novo e responda com atenção.</p>
          </div>
        </div>`;
      return;
    }

    const idx = onbSelectedIndex !== null ? onbSelectedIndex : currentIdx;
    const mod = ONBOARDING[idx];
    const isDone = onbProgress.completed.includes(mod.id);
    // Só entra em "modo revisão livre" (sem trava, sem quiz de novo) se
    // ela realmente acertou de primeira. Quem errou continua podendo
    // tentar de novo — precisa assistir e responder de novo pra corrigir
    // a nota e conseguir abrir o baú.
    const missedFirstTry = onbProgress.firstTry[mod.id] === false;
    const freeRewatch = isDone && !missedFirstTry;

    const statusLabel = freeRewatch ? 'CONCLUÍDO — REASSISTINDO' : missedFirstTry ? 'TENTE DE NOVO' : 'EM ANDAMENTO';
    const badgeContent = freeRewatch ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : String(idx + 1);

    wrap.innerHTML = `
      <div class="onb-module ${freeRewatch ? 'is-done' : 'is-current'}">
        <div class="onb-head">
          <div class="onb-badge">${badgeContent}</div>
          <div>
            <h4>${mod.title}</h4>
            <p>${mod.desc} · ${formatarDuracao(mod.duracaoSegundos, mod.minutes)}</p>
          </div>
          <span class="onb-status">${statusLabel}</span>
        </div>
        <div class="onb-body" id="onb-body-${mod.id}"></div>
      </div>
    `;
    renderModuleBody(mod, freeRewatch);
  }

  async function renderModuleBody(mod, isRewatch){
    const body = document.getElementById(`onb-body-${mod.id}`);
    if(!body) return;

    onbVideoUnlocked = false;
    onbMaxWatchedPct = 0;
    ultimoPctEnviado = -1; // senão o módulo seguinte herdaria o % do anterior
    clearInterval(onbYtTimer); // sem isso o módulo anterior continuaria contando

    body.innerHTML = '<div class="vid-hint"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Carregando o vídeo...</div>';

    let detalhe;
    try {
      detalhe = await carregarDetalheCurso(mod.id);
    } catch(e){
      body.innerHTML = `<div class="vid-hint"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Não foi possível carregar este módulo: ${e.message}</div>`;
      return;
    }

    // O quiz vem junto do detalhe, já sem a resposta certa — quem decide
    // se acertou é o servidor (antes o índice da correta ficava visível
    // no código da página, dava pra ver pelo inspecionar elemento).
    mod.questions = detalhe.questions || [];

    // video_url é a URL assinada do S3; quando a aula é do YouTube ela
    // vem nula e o id do vídeo é usado no lugar.
    // Playlist do YouTube: a Academy não controla o que é visto lá
    // dentro, então é sempre conteúdo extra — sem trava, sem barra de
    // progresso e sem pergunta.
    if(detalhe.video_source === 'playlist'){
      body.innerHTML = `
        <div class="vid-hint"><i class="fa-solid fa-circle-play" aria-hidden="true"></i> Conteúdo extra — assista os vídeos que quiser, na ordem que preferir. Não é necessário pra concluir a integração.</div>
        <div class="vid-player-wrap">
          <iframe src="https://www.youtube-nocookie.com/embed/videoseries?list=${encodeURIComponent(detalhe.youtube_id)}&rel=0"
                  title="${mod.title}" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen
                  style="width:100%;height:100%;border:0"></iframe>
          <button type="button" class="vid-fullscreen-btn" aria-label="Tela cheia">
            <i class="fa-solid fa-display" aria-hidden="true"></i>
          </button>
        </div>
      `;
      ligarBotaoTelaCheia(body);
      concluirAoAbrir(mod);
      return;
    }

    const videoSrc = detalhe.video_url;
    if(!videoSrc){
      if(!detalhe.youtube_id){
        body.innerHTML = '<div class="vid-hint"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Este módulo ainda não tem vídeo cadastrado.</div>';
        return;
      }

      // Aula por link do YouTube tem a MESMA trava do vídeo enviado: sem
      // controles e sem teclado, então não dá pra arrastar a barra até o
      // fim pra liberar a pergunta. Antes a pergunta abria de imediato
      // aqui, porque o <iframe> sozinho não informa o quanto foi visto —
      // a API do player resolve isso.
      body.innerHTML = isRewatch
        ? `
          <div class="vid-hint"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Modo revisão — pode avançar a barra à vontade.</div>
          <div class="vid-player-wrap"><div id="onb-yt-${mod.id}"></div></div>
        `
        : `
          <div class="vid-hint"><i class="fa-solid fa-lock" aria-hidden="true"></i> Assista até o fim para liberar a pergunta. Não é possível avançar a barra.</div>
          <div class="vid-player-wrap">
            <div id="onb-yt-${mod.id}"></div>
            <button type="button" class="vid-fullscreen-btn" data-alvo="onb-wrap-${mod.id}" aria-label="Tela cheia">
              <i class="fa-solid fa-display" aria-hidden="true"></i>
            </button>
            ${marcacaoControlesDeRevisao(mod.id)}
          </div>
          <div class="quiz-box" id="quiz-box-${mod.id}" hidden></div>
        `;

      loadYouTubeApi().then(() => {
        const player = new YT.Player(`onb-yt-${mod.id}`, {
          videoId: detalhe.youtube_id,
          playerVars: isRewatch
            ? { rel: 0, modestbranding: 1 }
            // fs:0 tira o botão de tela cheia DO YOUTUBE. Com ele, quem
            // ia pra tela cheia era o player, e aí o YouTube mostrava a
            // barra dele — dava pra ver e arrastar o vídeo. O botão de
            // tela cheia da Academy continua funcionando: ele expande a
            // moldura inteira, não o player, então controls:0 continua
            // valendo e a barra não aparece.
            : { controls: 0, disablekb: 1, rel: 0, modestbranding: 1, fs: 0 },
          events: {
            onStateChange: (e) => {
              if(e.data === YT.PlayerState.ENDED) onbSetWatchPct(mod, 1);
            }
          }
        });
        onbPlayer = player;
        ligarBotaoTelaCheia(body);
        if(isRewatch) return;

        const trava = criarTravaDeAvanco(false);
        ligarControlesDeRevisao(
          body,
          trava,
          (seg) => player.seekTo(seg, true),
          () => (typeof player.getCurrentTime === 'function' ? player.getCurrentTime() : 0)
        );

        // O player do YouTube não dispara "timeupdate" como o <video> e não
        // avisa quando a posição muda, então a posição é consultada de meio
        // em meio segundo. Como aqui não há barra do YouTube pra arrastar
        // (controls:0 e fs:0), isto é rede de segurança — pega atalho de
        // teclado ou qualquer outro caminho que escape.
        clearInterval(onbYtTimer);
        onbYtTimer = setInterval(() => {
          if(typeof player.getDuration !== 'function') return;
          const total = player.getDuration();
          if(total <= 0) return;
          registrarDuracao(mod.id, total);
          trava.definirDuracao(total);

          const voltarPara = trava.conferir(player.getCurrentTime());
          if(voltarPara !== null) player.seekTo(voltarPara, true);
          onbSetWatchPct(mod, trava.pct);
        }, 500);
      });
      return;
    }

    body.innerHTML = isRewatch
      ? `
        <div class="vid-hint"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> Modo revisão — pode avançar a barra à vontade.</div>
        <div class="vid-player-wrap">
          <video id="onb-video-${mod.id}" src="${videoSrc}" controls playsinline></video>
        </div>
      `
      : `
        <div class="vid-hint"><i class="fa-solid fa-lock" aria-hidden="true"></i> Assista até o fim para liberar a pergunta. Não é possível avançar a barra.</div>
        <div class="vid-player-wrap">
          <video id="onb-video-${mod.id}" src="${videoSrc}" playsinline></video>
          <button type="button" class="vid-play-btn" id="vid-play-${mod.id}" aria-label="Reproduzir vídeo">
            <i class="fa-solid fa-play" aria-hidden="true"></i>
          </button>
          <button type="button" class="vid-fullscreen-btn" aria-label="Tela cheia">
            <i class="fa-solid fa-display" aria-hidden="true"></i>
          </button>
          ${marcacaoControlesDeRevisao(mod.id)}
        </div>
        <div class="quiz-box" id="quiz-box-${mod.id}" hidden></div>
      `;

    const videoEl = document.getElementById(`onb-video-${mod.id}`);
    onbPlayer = videoEl;
    ligarBotaoTelaCheia(body);

    if(isRewatch){
      return; // controles nativos cuidam de tudo — não precisa rastrear progresso
    }

    const trava = criarTravaDeAvanco(false);
    ligarControlesDeRevisao(
      body,
      trava,
      (seg) => { videoEl.currentTime = seg; },
      () => videoEl.currentTime
    );

    // Esconder os controles não basta pra travar o avanço: em tela cheia
    // o navegador mostra os próprios controles, e aí dava pra arrastar a
    // barra e pular o vídeo. A regra é aplicada na posição do vídeo, então
    // vale por qualquer caminho que a pessoa tente. Voltar continua
    // liberado — o limite é só pra frente.
    const travarBusca = () => {
      trava.definirDuracao(videoEl.duration);
      const voltarPara = trava.aoBuscar(videoEl.currentTime);
      if(voltarPara !== null) videoEl.currentTime = voltarPara;
    };
    // "seeking" avisa quando o salto começa; "seeked", quando termina. Os
    // dois porque nem todo navegador aceita corrigir a posição já no
    // primeiro — sem o segundo, o salto passava batido em alguns.
    videoEl.addEventListener('seeking', travarBusca);
    videoEl.addEventListener('seeked', travarBusca);

    // Sem controles nativos nesse modo (trava avançar a barra) — um botão
    // de play próprio, e a barra de progresso é só visual (não clicável),
    // igual já era com o player do YouTube antes.
    const playBtn = document.getElementById(`vid-play-${mod.id}`);
    playBtn.addEventListener('click', () => {
      videoEl.play();
      playBtn.classList.add('is-hidden');
    });
    videoEl.addEventListener('pause', () => {
      if(!videoEl.ended) playBtn.classList.remove('is-hidden');
    });
    videoEl.addEventListener('play', () => playBtn.classList.add('is-hidden'));
    // O <video> só conhece a duração depois de ler os metadados.
    videoEl.addEventListener('loadedmetadata', () => {
      registrarDuracao(mod.id, videoEl.duration);
      trava.definirDuracao(videoEl.duration);
    });
    videoEl.addEventListener('timeupdate', () => {
      if(!videoEl.duration) return;
      trava.definirDuracao(videoEl.duration);
      // O progresso vem da trava, não da posição do vídeo: era daí que
      // saía o furo — a posição sobe com o arrasto, o limite não.
      const voltarPara = trava.conferir(videoEl.currentTime);
      if(voltarPara !== null) videoEl.currentTime = voltarPara;
      onbSetWatchPct(mod, trava.pct);
    });
    videoEl.addEventListener('ended', () => onbSetWatchPct(mod, 1));
  }

  // O servidor recusa a resposta do quiz de quem não assistiu 95% do
  // vídeo, e ele só sabe disso pelo que enviamos aqui. Mandar a cada
  // "timeupdate" seriam ~4 requisições por segundo, então só avisamos a
  // cada 5 pontos percentuais — e sempre no 100%, que é o que libera.
  let ultimoPctEnviado = -1;
  async function enviarProgresso(courseId, pct){
    const inteiro = Math.min(Math.round(pct * 100), 100);
    if(inteiro <= ultimoPctEnviado || (inteiro % 5 !== 0 && inteiro < 100)) return;
    ultimoPctEnviado = inteiro;
    try {
      await apiPost('api/progress/update.php', { course_id: courseId, watched_pct: inteiro });
    } catch(e){
      // Não interrompe a aula: no pior caso o quiz recusa e a pessoa
      // reassiste. Derrubar o player por causa disso seria pior.
      console.warn('[progresso] não consegui registrar:', e.message);
    }
  }

  // Tela cheia na moldura inteira (e não só no <video>): assim a barra
  // de quanto foi assistido continua visível, e no caso do YouTube o
  // iframe vai junto.
  // ================================================================
  // ---------- Trava de avanço do vídeo ----------
  // ================================================================
  // Na primeira vez, a pessoa só anda pra frente assistindo: voltar é
  // livre, adiantar não. Depois de 99% assistido o vídeo conta como visto
  // e a barra libera de vez.
  //
  // A versão anterior tinha um furo que se explicava sozinho: o "já vi até
  // aqui" era alimentado com a posição atual do vídeo, fosse ela qual
  // fosse. Arrastar a barra pra frente subia justamente o número que
  // deveria barrar o arrasto — a trava autorizava o pulo que ela existia
  // pra impedir. Por isso dava pra pular nos dois players.
  //
  // Aqui o limite só sobe quando o tempo anda sozinho, no ritmo de quem
  // está assistindo. A referência é o relógio de parede: durante a
  // reprodução o vídeo avança mais ou menos o mesmo tanto que o tempo real;
  // um pulo avança minutos em milissegundos. Comparar com o relógio (em vez
  // de usar um limite fixo em segundos) é o que faz a trava sobreviver a
  // travadas de buffer e à aba em segundo plano, onde a amostragem atrasa e
  // um avanço legítimo pareceria salto.
  const TRAVA_PASSO_MIN = 1.0;   // s — folga mínima, pra amostras muito juntas
  const TRAVA_PCT_LIVRE = 0.99;  // assistiu isso, pode avançar à vontade

  function criarTravaDeAvanco(jaLiberado){
    let maxSeg = 0;        // até onde a pessoa realmente assistiu (segundos)
    let ultimoTempo = 0;   // última posição observada no vídeo
    let ultimoRelogio = 0; // performance.now() da última observação
    let duracao = 0;
    let livre = !!jaLiberado;

    return {
      get livre(){ return livre; },
      get duracaoTotal(){ return duracao; },
      get maxSegundos(){ return livre && duracao ? duracao : maxSeg; },
      get pct(){
        if(duracao <= 0) return 0;
        return Math.min((livre ? duracao : maxSeg) / duracao, 1);
      },
      definirDuracao(d){ if(d > 0 && isFinite(d)) duracao = d; },

      /**
       * Posição observada durante a reprodução. Devolve o segundo pra onde
       * o vídeo deve voltar, ou null quando está tudo certo.
       */
      conferir(atual){
        if(livre || duracao <= 0 || !isFinite(atual)) return null;

        const agora = (typeof performance !== 'undefined' ? performance.now() : Date.now());
        const decorrido = ultimoRelogio ? (agora - ultimoRelogio) / 1000 : 0;
        ultimoRelogio = agora;

        const passo = atual - ultimoTempo;
        ultimoTempo = atual;

        // Andou junto com o relógio: é reprodução. A margem cobre 1.5x de
        // velocidade e o atraso normal entre uma amostra e outra.
        if(passo >= 0 && passo <= Math.max(TRAVA_PASSO_MIN, decorrido * 1.6 + 0.5)){
          if(atual > maxSeg) maxSeg = atual;
          if(maxSeg / duracao >= TRAVA_PCT_LIVRE){ maxSeg = duracao; livre = true; }
          return null;
        }

        // Voltou, ou está revendo trecho que já assistiu: pode.
        if(atual <= maxSeg + 0.3) return null;

        ultimoTempo = maxSeg;
        return maxSeg;
      },

      /**
       * Tentativa explícita de mudar de posição — o evento "seeking" do
       * <video> ou um clique na nossa barra. Aqui não há dúvida nenhuma
       * sobre a intenção, então nem entra a conta do relógio.
       */
      aoBuscar(destino){
        if(livre || !isFinite(destino)) return null;
        if(destino <= maxSeg + 0.3){ ultimoTempo = destino; return null; }
        ultimoTempo = maxSeg;
        return maxSeg;
      }
    };
  }

  // A barra própria da primeira vez aceita clique, mas só dentro do trecho
  // já assistido. É o que permite voltar sem abrir a porta pra frente: a
  // barra nativa (do <video> ou do YouTube) não tem como oferecer uma coisa
  // sem a outra — ou dá as duas, ou não dá nenhuma.
  function ligarControlesDeRevisao(escopo, trava, irPara, posicaoAtual){
    const bar = escopo.querySelector('.vid-watch-bar');
    if(bar){
      bar.classList.add('is-clicavel');
      bar.title = 'Clique para rever um trecho que você já assistiu';
      bar.addEventListener('click', (e) => {
        const total = trava.duracaoTotal;
        if(!total) return;
        const r = bar.getBoundingClientRect();
        const alvo = ((e.clientX - r.left) / r.width) * total;
        const barrado = trava.aoBuscar(alvo);
        if(barrado !== null){
          bar.classList.add('is-negado');
          setTimeout(() => bar.classList.remove('is-negado'), 400);
          return; // clicou adiante do que assistiu — fica onde está
        }
        irPara(Math.max(alvo, 0));
      });
    }

    const voltarBtn = escopo.querySelector('.vid-voltar-btn');
    if(voltarBtn){
      voltarBtn.addEventListener('click', () => {
        const destino = Math.max(posicaoAtual() - 10, 0);
        trava.aoBuscar(destino);
        irPara(destino);
      });
    }
  }

  function atualizarBarraDeRevisao(id, trava){
    const fill = document.getElementById(`vid-watch-fill-${id}`);
    if(fill) fill.style.width = Math.min(trava.pct * 100, 100) + '%';
  }

  // Marcação dos controles da primeira vez. Só a barra e o "voltar 10s":
  // avançar não tem botão porque não existe avançar aqui.
  function marcacaoControlesDeRevisao(id){
    return `
      <div class="vid-watch-bar"><div class="vid-watch-fill" id="vid-watch-fill-${id}"></div></div>
      <button type="button" class="vid-voltar-btn" aria-label="Voltar 10 segundos">&minus;10s</button>
    `;
  }

  function ligarBotaoTelaCheia(escopo){
    const btn = escopo.querySelector('.vid-fullscreen-btn');
    if(!btn) return;
    btn.addEventListener('click', () => {
      const alvo = btn.closest('.vid-player-wrap');
      if(!alvo) return;
      if(document.fullscreenElement){
        document.exitFullscreen();
      } else if(alvo.requestFullscreen){
        alvo.requestFullscreen().catch(e => console.warn('[tela cheia]', e.message));
      }
    });
  }

  function onbSetWatchPct(mod, pct){
    onbMaxWatchedPct = Math.max(onbMaxWatchedPct, pct);
    const fill = document.getElementById(`vid-watch-fill-${mod.id}`);
    if(fill) fill.style.width = Math.min(onbMaxWatchedPct * 100, 100) + '%';

    enviarProgresso(mod.id, onbMaxWatchedPct);

    if(!onbVideoUnlocked && onbMaxWatchedPct >= ONB_PASS_THRESHOLD){
      onbVideoUnlocked = true;
      if(mod.temQuiz){
        revealQuiz(mod);
      } else {
        concluirModuloSemPergunta(mod);
      }
    }
  }

  // Aula sem pergunta: assistir até o fim é o que conclui. Quem marca
  // isso é o servidor (progress/update.php), então aqui só garantimos o
  // envio dos 100% e recarregamos a trilha pra liberar o próximo módulo.
  // Aula opcional (playlist, extras) não tem como ser concluída pelo
  // caminho normal: não há vídeo pra medir nem pergunta pra responder.
  // Abrir já conta como cumprida, senão ela ficaria pendente pra sempre
  // e apareceria como "não concluída" no relatório de quem assistiu.
  async function concluirAoAbrir(mod){
    if(mod.obrigatorio !== false) return;
    if(onbProgress.completed.includes(mod.id)) return;
    try {
      await apiPost('api/progress/update.php', { course_id: mod.id, watched_pct: 100 });
      onbProgress.completed.push(mod.id);
      saveOnboardingProgress(onbProgress);
      renderProgressPanel();
      renderOnbPath(); // marca a casa como concluída sem redesenhar o vídeo
    } catch(e){
      console.warn('[opcional] não consegui registrar:', e.message);
    }
  }

  async function concluirModuloSemPergunta(mod){
    const caixa = document.getElementById(`quiz-box-${mod.id}`);
    try {
      await apiPost('api/progress/update.php', { course_id: mod.id, watched_pct: 100 });
      if(!onbProgress.completed.includes(mod.id)){
        onbProgress.completed.push(mod.id);
        saveOnboardingProgress(onbProgress);
      }
      if(caixa){
        caixa.hidden = false;
        caixa.innerHTML = '<div class="quiz-feedback ok"><i class="fa-solid fa-check" aria-hidden="true"></i> Módulo concluído. Próxima etapa liberada.</div>';
      }
      renderProgressPanel();
      setTimeout(renderOnboarding, 900);
    } catch(e){
      if(caixa){
        caixa.hidden = false;
        caixa.innerHTML = `<div class="quiz-feedback bad"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> ${e.message}</div>`;
      }
    }
  }

  let onbQuizAnsweredThisSession = false; // reseta toda vez que a pergunta reaparece

  function revealQuiz(mod){
    const quizEl = document.getElementById(`quiz-box-${mod.id}`);
    if(!quizEl) return;
    quizEl.hidden = false;
    onbQuizAnsweredThisSession = false; // nova "sessão" — só a 1ª resposta dessa vez conta

    const question = mod.questions[0];
    if(!question){
      quizEl.hidden = true; // aula sem pergunta cadastrada
      return;
    }
    // Os textos e ids vêm do banco (question_text/option_text), e cada
    // opção carrega o id real — é o que o servidor usa pra conferir a
    // resposta, já que a alternativa certa não é mais enviada ao browser.
    quizEl.innerHTML = `
      <h5>${question.question_text}</h5>
      <div class="quiz-options">
        ${question.options.map(opt => `
          <button type="button" class="quiz-option" data-option-id="${opt.id}">
            <span>${opt.option_text}</span>
          </button>
        `).join('')}
      </div>
      <div class="quiz-feedback" id="quiz-feedback-${mod.id}" hidden></div>
    `;

    quizEl.querySelectorAll('.quiz-option').forEach(btn => {
      btn.addEventListener('click', () => onbAnswerQuestion(mod, question, btn, quizEl));
    });
  }

  async function onbAnswerQuestion(mod, question, btn, quizEl){
    const allOptions = quizEl.querySelectorAll('.quiz-option');
    const feedbackEl = document.getElementById(`quiz-feedback-${mod.id}`);

    allOptions.forEach(o => o.disabled = true);

    // Quem confere é o servidor: ele valida a opção, registra a
    // tentativa e é quem marca o módulo como concluído.
    let isCorrect;
    try {
      const r = await apiPost('api/quiz/submit.php', {
        question_id: question.id,
        option_id: parseInt(btn.dataset.optionId, 10),
      });
      isCorrect = !!r.correct;
    } catch(e){
      feedbackEl.hidden = false;
      feedbackEl.className = 'quiz-feedback bad';
      feedbackEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> ${e.message}`;
      allOptions.forEach(o => o.disabled = false);
      return;
    }

    // Guarda o resultado só da PRIMEIRA resposta dessa sessão (não do
    // retry imediato de 1.2s que já existia) — senão a pessoa sempre
    // acabava com "acertou" eventualmente, e a exigência de 75% não
    // significaria nada. Só reassistir de novo (nova sessão) permite
    // tentar consertar o resultado.
    if(!onbQuizAnsweredThisSession){
      onbQuizAnsweredThisSession = true;
      onbProgress.firstTry[mod.id] = isCorrect;
      saveOnboardingProgress(onbProgress);
    }

    if(isCorrect){
      btn.classList.add('correct');
      btn.insertAdjacentHTML('beforeend', '<i class="fa-solid fa-check opt-icon" aria-hidden="true"></i>');
      feedbackEl.hidden = false;
      feedbackEl.className = 'quiz-feedback ok';
      feedbackEl.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Rota concluída. Você já sabe navegar por aqui.';

      if(!onbProgress.completed.includes(mod.id)){
        onbProgress.completed.push(mod.id);
        saveOnboardingProgress(onbProgress);
      }
      renderProgressPanel();
      setTimeout(renderOnboarding, 900);
    } else {
      btn.classList.add('incorrect');
      btn.insertAdjacentHTML('beforeend', '<i class="fa-solid fa-circle-xmark opt-icon" aria-hidden="true"></i>');
      feedbackEl.hidden = false;
      feedbackEl.className = 'quiz-feedback bad';
      feedbackEl.innerHTML = '<i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Quase lá — dá mais uma olhada e tenta de novo.';

      // permite tentar novamente após um instante
      setTimeout(() => {
        allOptions.forEach(o => {
          o.disabled = false;
          o.classList.remove('incorrect');
        });
        feedbackEl.hidden = true;
      }, 1200);
    }
  }

  renderOnboarding();

  // ---------- Sombra no header ao rolar ----------
  const headerEl = document.querySelector('header');
  const onScroll = () => headerEl.classList.toggle('is-scrolled', window.scrollY > 8);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // ---------- Dados dos tutoriais (vídeos do canal da MSE no YouTube) ----------
  // Troque "youtubeId" pelo ID real de cada vídeo quando estiver publicado
  // (é o trecho depois de "v=" na URL do YouTube).
  // O campo "cat" usa o slug da área real do Portal MSE (mesma estrutura do menu lateral).
  // Quando o backend estiver pronto, dá pra usar esse mesmo "cat" para filtrar
  // automaticamente os tutoriais pela área do colaborador logado.
  // Catalogo carregado do banco em carregarConteudo(). Os cursos que
  // ficavam fixos aqui usavam todos o mesmo video de exemplo do
  // YouTube, entao nenhum deles era conteudo real.
  let courses = [];

  // O carrossel e a busca mostravam exemplos escritos à mão ("Como
  // solicitar férias", "Ex: férias, contracheque..."), que envelheciam
  // sozinhos: citavam aulas que podiam nem existir mais. Agora saem do
  // próprio catálogo, sorteados a cada carregamento da página — assim a
  // pessoa vê sugestões que existem de verdade e vão mudando.
  function sortear(lista, quantos){
    const copia = lista.slice();
    for(let i = copia.length - 1; i > 0; i--){
      const j = Math.floor(Math.random() * (i + 1));
      [copia[i], copia[j]] = [copia[j], copia[i]];
    }
    return copia.slice(0, quantos);
  }

  function atualizarDestaquesComCursosReais(){
    // Sem catálogo, fica o slide de abertura. Melhor um slide só e
    // verdadeiro do que cinco anunciando aula que não existe.
    if(!courses.length) return;

    // O carrossel passa a ter o tamanho do catálogo, no máximo o número de
    // imagens disponíveis. Antes eram sempre cinco slides e só os primeiros
    // recebiam curso real — com três cursos cadastrados, dois slides
    // seguiam anunciando as aulas de exemplo escritas no código.
    const escolhidos = sortear(courses, IMAGENS_DO_CARROSSEL.length);
    slides.length = 0;
    escolhidos.forEach((curso, i) => {
      slides.push({
        img: IMAGENS_DO_CARROSSEL[i],
        title: curso.title,
        lead: curso.label
          ? `Tutorial da área <b>${curso.label}</b>. ${curso.desc || ''}`.trim()
          : (curso.desc || '')
      });
    });

    montarBolinhas();
    currentSlide = 0;
    renderSlideInstant(currentSlide);

    // Busca: sugere títulos que existem de verdade, em vez de exemplos fixos.
    const exemplos = sortear(courses, 3).map(c => c.title);
    if(exemplos.length){
      const dica = 'Ex: ' + exemplos.join(', ');
      ['heroSearch', 'headerSearch'].forEach(id => {
        const campo = document.getElementById(id);
        if(campo) campo.placeholder = dica;
      });
    }
  }

  // Traduz o formato da API pro formato que o resto da tela já esperava,
  // pra não precisar reescrever gamificação, favoritos, busca e painel
  // de progresso — que juntos usam essas listas em mais de 30 lugares.
  async function carregarConteudo(){
    const [trilha, catalogo] = await Promise.all([
      apiGet('api/courses/list.php?type=onboarding'),
      apiGet('api/courses/list.php?type=curso&scope=all'),
    ]);

    ONBOARDING = (trilha.courses || []).map(c => ({
      id: c.id,
      title: c.title,
      desc: c.description || '',
      minutes: c.duration_minutes,
      duracaoSegundos: c.duration_seconds,
      youtubeId: c.youtube_id,
      temQuiz: !!c.tem_quiz,
      obrigatorio: c.obrigatorio !== false,
      videoSource: c.video_source,
      questions: [], // preenchido ao abrir o módulo (vem do detail.php)
    }));

    courses = (catalogo.courses || []).map(c => ({
      id: c.id,
      cat: c.area_slug,
      label: c.area_name || '',
      title: c.title,
      desc: c.description || '',
      time: formatarDuracao(c.duration_seconds, c.duration_minutes),
      duracaoSegundos: c.duration_seconds,
      youtubeId: c.youtube_id,
      questions: [],
    }));

    await carregarProgressoServidor();
    conteudoCarregado = true;
    atualizarDestaquesComCursosReais();
  }

  // O progresso agora mora no banco (vale em qualquer navegador, e é o
  // que alimenta o relatório "Quem assistiu"). O localStorage continua
  // sendo escrito só como espelho, pra tela não piscar enquanto carrega.
  async function carregarProgressoServidor(){
    const dados = await apiGet('api/progress/list.php');
    const concluidos = (dados.progress || [])
      .filter(p => p.status === 'concluido')
      .map(p => Number(p.course_id));

    const idsTrilha = new Set(ONBOARDING.map(m => m.id));
    onbProgress.completed = concluidos.filter(id => idsTrilha.has(id));
    catalogProgress.completed = concluidos.filter(id => !idsTrilha.has(id));
  }

  // ---------- Progresso do catálogo (pontos + concluídos) ----------
  // Front-end apenas por enquanto (localStorage) — quando o backend entrar,
  // isso troca por chamadas à API, igual já está documentado pra trilha.
  const CATALOG_STORAGE_KEY = 'mse_academy_catalog_v1';

  function loadCatalogProgress(){
    try{
      const raw = store.getItem(CATALOG_STORAGE_KEY);
      return raw ? JSON.parse(raw) : { completed: [], points: 0 };
    }catch(e){
      return { completed: [], points: 0 };
    }
  }
  function saveCatalogProgress(progress){
    store.setItem(CATALOG_STORAGE_KEY, JSON.stringify(progress));
  }
  let catalogProgress = loadCatalogProgress();

  // ================================================================
  // ---------- Painel "Seu progresso" — Passaporte de Bordo MSE ----------
  // ================================================================
  // Os 5 níveis aqui são por MARCO cumprido (não por pontos acumulados),
  // exatamente como no projeto aprovado — cada nível exige que o anterior
  // também esteja cumprido, criando uma progressão sequencial de verdade.
  const ONB_POINTS_PER_MODULE = 10;

  // A pedido: o texto "X pontos · Y tarefas" do painel conta só os vídeos
  // do catálogo de cursos, não os módulos de integração.
  function getTotalPoints(){
    return catalogProgress.points;
  }
  function getTotalCompleted(){
    return catalogProgress.completed.length;
  }

  function isAreaFullyDone(slug){
    const areaCourses = courses.filter(c => c.cat === slug);
    if(areaCourses.length === 0) return false;
    return areaCourses.every(c => catalogProgress.completed.includes(c.id));
  }

  function getDistinctAreasCompleted(){
    return new Set(
      catalogProgress.completed
        .map(id => courses.find(c => c.id === id)?.cat)
        .filter(Boolean)
    );
  }

  const LEVELS = [
    {
      name: 'Tripulante Novato',
      check: () => true, // estado inicial — todo mundo começa aqui
      progress: () => onbProgress.completed.length / (ONBOARDING.length || 1),
    },
    {
      name: 'Navegador',
      check: () => onbProgress.completed.length >= ONBOARDING.length,
      progress: () => Math.min(getDistinctAreasCompleted().size, 3) / 3,
    },
    {
      name: 'Explorador',
      check: () => getDistinctAreasCompleted().size >= 3,
      progress: () => {
        const slugs = [...new Set(courses.map(c => c.cat))];
        const ratios = slugs.map(slug => {
          const areaCourses = courses.filter(c => c.cat === slug);
          if(areaCourses.length === 0) return 0;
          const done = areaCourses.filter(c => catalogProgress.completed.includes(c.id)).length;
          return done / areaCourses.length;
        });
        return ratios.length ? Math.max(...ratios) : 0;
      },
    },
    {
      name: 'Piloto de Rota',
      check: () => [...new Set(courses.map(c => c.cat))].some(isAreaFullyDone),
      progress: () => courses.length ? catalogProgress.completed.length / courses.length : 0,
    },
    {
      name: 'Comandante de Bordo',
      check: () => {
        const allCatalogDone = courses.every(c => catalogProgress.completed.includes(c.id));
        const onboardingDone = onbProgress.completed.length >= ONBOARDING.length;
        return allCatalogDone && onboardingDone;
      },
      progress: () => 1,
    },
  ];

  // Avança em ordem estrita: só conta o marco se TODOS os anteriores
  // também já foram cumpridos (mesma regra usada no tabuleiro que testamos
  // antes — evita "pular" nível fora de ordem).
  function getCurrentLevelIndex(){
    let idx = 0;
    for(let i = 1; i < LEVELS.length; i++){
      if(!LEVELS[i].check()) break;
      idx = i;
    }
    return idx;
  }

  const BADGES = [
    {
      id: 'carimbo-embarque', label: 'Carimbo de Embarque', icon: 'fa-check',
      check: () => onbProgress.completed.length >= 1
    },
    {
      id: 'bussola-certeira', label: 'Bússola Certeira', icon: 'fa-check',
      // "acertou um quiz de 1ª tentativa" no projeto aprovado — o site hoje
      // não guarda se foi na 1ª tentativa ou não, só se concluiu; uso a
      // 1ª conclusão de qualquer quiz (trilha ou catálogo) como aproximação
      // até existir esse rastreio mais fino.
      check: () => onbProgress.completed.length >= 1 || catalogProgress.completed.length >= 1
    },
    {
      id: 'radar-ativado', label: 'Radar Ativado', icon: 'fa-check',
      check: () => onbProgress.completed.length >= ONBOARDING.length
    },
    {
      id: 'pratica-de-bordo', label: 'Prática de Bordo', icon: 'fa-check',
      // "1ª ação prática real no portal" (Etapa 3 do projeto) ainda não
      // existe como etapa separada no código — por enquanto uso a 1ª
      // mini-aula concluída do catálogo como aproximação (é a etapa mais
      // próxima de "prática" que já existe hoje).
      check: () => catalogProgress.completed.length >= 1
    },
    {
      id: 'rota-dominada', label: 'Rota Dominada', icon: 'fa-check',
      check: () => [...new Set(courses.map(c => c.cat))].some(isAreaFullyDone)
    },
    {
      id: 'mapa-completo', label: 'Mapa Completo', icon: 'fa-check',
      check: () => {
        const allCatalogDone = courses.every(c => catalogProgress.completed.includes(c.id));
        const onboardingDone = onbProgress.completed.length >= ONBOARDING.length;
        return allCatalogDone && onboardingDone;
      }
    },
  ];

  // ================================================================
  // ---------- Raios elétricos na barra de progresso ----------
  // ================================================================
  // Mesmo motor testado no protótipo: gera o raio com deslocamento de
  // ponto médio (a forma clássica de desenhar eletricidade em jogos),
  // recalcula o formato a cada quadro (dá a sensação de "mexendo"), e
  // deixa o raio escapar pra fora da barra fina — mais em intensidades altas.
  function generateBolt(x1, y1, x2, y2, displace, detail){
    if(displace < detail) return [[x1, y1], [x2, y2]];
    const midX = (x1 + x2) / 2 + (Math.random() - 0.5) * displace;
    const midY = (y1 + y2) / 2 + (Math.random() - 0.5) * displace;
    const left = generateBolt(x1, y1, midX, midY, displace / 2, detail);
    const right = generateBolt(midX, midY, x2, y2, displace / 2, detail);
    return [...left.slice(0, -1), ...right];
  }

  const BOLT_TIERS = {
    weak:         { min: 0,  spawnChance: 0.030, maxBolts: 2, life: 10, glow: 9,  branchChance: 0.05, whiteMix: 0.05, escape: 0.35 },
    moderate:     { min: 31, spawnChance: 0.10,   maxBolts: 3, life: 14, glow: 15, branchChance: 0.2,  whiteMix: 0.15, escape: 0.55 },
    intense:      { min: 71, spawnChance: 0.25,   maxBolts: 5, life: 17, glow: 22, branchChance: 0.4,  whiteMix: 0.3,  escape: 0.8 },
    supercharged: { min: 100,spawnChance: 0.48,   maxBolts: 7, life: 21, glow: 30, branchChance: 0.65, whiteMix: 0.45, escape: 1.05 },
  };
  function getBoltTier(pct){
    if(pct >= 100) return 'supercharged';
    if(pct >= 71) return 'intense';
    if(pct >= 31) return 'moderate';
    return 'weak';
  }

  const lightningCanvas = document.getElementById('progressLightning');
  const lightningCtx = lightningCanvas ? lightningCanvas.getContext('2d') : null;
  const progressBarTrack = lightningCanvas ? lightningCanvas.parentElement : null;
  let lightningPct = 0;
  let lightningBolts = [];

  function resizeLightningCanvas(){
    if(!lightningCanvas) return;
    const dpr = window.devicePixelRatio || 1;
    const rect = lightningCanvas.getBoundingClientRect();
    lightningCanvas.width = rect.width * dpr;
    lightningCanvas.height = rect.height * dpr;
    lightningCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }
  if(lightningCanvas){
    window.addEventListener('resize', resizeLightningCanvas);
    resizeLightningCanvas();
  }

  function makeLightningBolt(x1, y1, x2, y2, life, whiteMix, isBranch){
    return {
      x1, y1, x2, y2,
      points: generateBolt(x1, y1, x2, y2, isBranch ? 8 : 12, 3),
      life, maxLife: life,
      isWhite: Math.random() < whiteMix,
      isBranch: !!isBranch,
    };
  }

  function spawnLightningBolt(){
    const trackRect = progressBarTrack.getBoundingClientRect();
    const fillWidthPx = trackRect.width * (lightningPct / 100);
    if(fillWidthPx < 10) return;

    const tierConf = BOLT_TIERS[getBoltTier(lightningPct)];
    const canvasRect = lightningCanvas.getBoundingClientRect();
    const centerY = (trackRect.top - canvasRect.top) + trackRect.height / 2;

    const x1 = Math.random() * Math.max(fillWidthPx - 24, 4);
    const boltLen = 16 + Math.random() * 30;
    const x2 = Math.min(x1 + boltLen, fillWidthPx - 2);

    const escapeReach = 4 + tierConf.escape * 13;
    const y1 = centerY + (Math.random() - 0.5) * escapeReach * 0.4;
    const y2 = centerY + (Math.random() - 0.5) * escapeReach;

    lightningBolts.push(makeLightningBolt(x1, y1, x2, y2, tierConf.life, tierConf.whiteMix));

    if(Math.random() < tierConf.branchChance){
      const midX = (x1 + x2) / 2;
      const midY = (y1 + y2) / 2;
      const bx2 = midX + (Math.random() - 0.5) * 24;
      const by2 = centerY + (Math.random() < 0.5 ? -1 : 1) * (escapeReach * 0.7 + 4);
      lightningBolts.push(makeLightningBolt(midX, midY, bx2, by2, tierConf.life * 0.6, tierConf.whiteMix + 0.2, true));
    }
  }

  function drawLightningBolt(bolt){
    const alpha = bolt.life / bolt.maxLife;
    // Amarelo saturado de verdade (não mais um tom claro/esbranquiçado) —
    // esse fundo aqui é branco, cor clara demais simplesmente sumia nele.
    const color = bolt.isWhite ? '255,205,40' : '240,185,11';
    lightningCtx.save();
    lightningCtx.globalAlpha = alpha;
    lightningCtx.strokeStyle = `rgba(${color}, ${alpha})`;
    lightningCtx.lineWidth = bolt.isBranch ? 1.6 : 2.4;
    lightningCtx.shadowColor = '#F0B90B';
    lightningCtx.shadowBlur = BOLT_TIERS[getBoltTier(lightningPct)].glow * alpha;
    lightningCtx.beginPath();
    bolt.points.forEach(([px, py], i) => { if(i === 0) lightningCtx.moveTo(px, py); else lightningCtx.lineTo(px, py); });
    lightningCtx.stroke();
    // núcleo mais escuro/dourado por cima (branco sumiria no fundo claro do card)
    lightningCtx.lineWidth = bolt.isBranch ? 0.7 : 1;
    lightningCtx.strokeStyle = `rgba(150,105,5,${0.8 * alpha})`;
    lightningCtx.shadowBlur = 0;
    lightningCtx.stroke();
    lightningCtx.restore();
  }

  function lightningTick(){
    if(lightningCanvas){
      const canvasRect = lightningCanvas.getBoundingClientRect();
      lightningCtx.clearRect(0, 0, canvasRect.width, canvasRect.height);

      const tierConf = BOLT_TIERS[getBoltTier(lightningPct)];
      if(lightningBolts.length < tierConf.maxBolts && Math.random() < tierConf.spawnChance){
        spawnLightningBolt();
      }
      lightningBolts = lightningBolts.filter(b => b.life > 0);
      lightningBolts.forEach(b => {
        b.points = generateBolt(b.x1, b.y1, b.x2, b.y2, b.isBranch ? 8 : 12, 3);
        drawLightningBolt(b);
        b.life -= 1;
      });
    }
    requestAnimationFrame(lightningTick);
  }
  if(lightningCanvas && !prefersReducedMotion) requestAnimationFrame(lightningTick);

  function renderProgressPanel(){
    // Com as listas ainda vazias, checagens do tipo courses.every(...)
    // retornam true e o painel daria nível máximo e todos os troféus
    // antes do conteúdo chegar.
    if(!conteudoCarregado) return;
    const points = getTotalPoints();
    const levelIdx = getCurrentLevelIndex();
    const level = LEVELS[levelIdx];
    // A pedido: a barra em si (o preenchimento colorido) conta só os vídeos
    // do catálogo de cursos — não usa mais o progresso do nível atual, que
    // misturava integração + catálogo.
    const pct = courses.length ? Math.round((catalogProgress.completed.length / courses.length) * 100) : 0;

    document.getElementById('progressLevelBadge').textContent = levelIdx + 1;
    document.getElementById('progressLevelName').textContent = level.name;
    document.getElementById('progressLevelSub').textContent =
      `${points} pontos de experiência · ${getTotalCompleted()} tarefa(s) concluída(s)`;

    document.getElementById('progressBarFill').style.width = pct + '%';
    lightningPct = pct;
    document.getElementById('progressBarFill').classList.toggle('tier-supercharged', pct >= 100);

    const badgesWrap = document.getElementById('progressBadges');
    badgesWrap.innerHTML = '';
    BADGES.forEach(b => {
      const unlocked = b.check();
      const chip = document.createElement('span');
      chip.className = 'progress-badge' + (unlocked ? ' unlocked' : '');
      chip.innerHTML = `<i class="fa-solid ${unlocked ? b.icon : 'fa-lock'}" aria-hidden="true"></i> ${b.label}`;
      badgesWrap.appendChild(chip);
    });

    // Contagem discreta em cada card de área ("3 de 5 concluídos")
    document.querySelectorAll('.area-card').forEach(card => {
      const slug = card.dataset.area;
      const areaCourses = courses.filter(c => c.cat === slug);
      let tag = card.querySelector('.area-progress');
      if(areaCourses.length === 0){
        if(tag) tag.remove();
        return;
      }
      const done = areaCourses.filter(c => catalogProgress.completed.includes(c.id)).length;
      if(!tag){
        tag = document.createElement('span');
        tag.className = 'area-progress';
        card.querySelector('div:last-child')?.appendChild(tag) || card.appendChild(tag);
      }
      tag.textContent = `${done} de ${areaCourses.length} concluído(s)`;
      tag.classList.toggle('complete', done === areaCourses.length);
    });
  }

  function arrowIcon(){
    return '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>';
  }

  // Trava de scroll do body compartilhada entre as duas telas (área e vídeo).
  // Usa contador em vez de true/false direto: se a pessoa abre um vídeo de
  // dentro da tela de área e fecha só o vídeo, o scroll não pode voltar
  // enquanto a tela de área ainda estiver aberta por baixo.
  let scrollLockCount = 0;
  function lockBodyScroll(){
    scrollLockCount++;
    document.body.style.overflow = 'hidden';
  }
  function unlockBodyScroll(){
    scrollLockCount = Math.max(0, scrollLockCount - 1);
    if(scrollLockCount === 0) document.body.style.overflow = '';
  }

  const FAV_STORAGE_KEY = 'mse_academy_favorites_v1';
  function loadFavorites(){
    try{
      const raw = store.getItem(FAV_STORAGE_KEY);
      return raw ? JSON.parse(raw) : { ids: [] };
    }catch(e){
      return { ids: [] };
    }
  }
  function saveFavorites(favs){
    store.setItem(FAV_STORAGE_KEY, JSON.stringify(favs));
  }
  let favorites = loadFavorites();

  function updateFavCount(){
    const el = document.getElementById('favCount');
    if(el) el.textContent = favorites.ids.length;
  }

  function buildCourseCard(c){
    const isDone = catalogProgress.completed.includes(c.id);
    const isFav = favorites.ids.includes(c.id);
    // Vira <div role="button"> (não <button>) porque tem um botão de
    // favoritar de verdade dentro — <button> dentro de <button> não é
    // válido em HTML e quebra o comportamento de clique do navegador.
    const card = document.createElement('div');
    card.className = 'course-card';
    card.tabIndex = 0;
    card.setAttribute('role', 'button');
    card.setAttribute('aria-label', `Assistir: ${c.title}`);
    card.innerHTML = `
      ${isDone ? '<span class="done-tag"><i class="fa-solid fa-check" aria-hidden="true"></i> Concluído</span>' : ''}
      <button type="button" class="fav-toggle${isFav ? ' is-fav' : ''}" aria-label="${isFav ? 'Remover dos favoritos' : 'Adicionar aos favoritos'}" aria-pressed="${isFav}">
        <i class="fa-solid fa-star" aria-hidden="true"></i>
      </button>
      <div class="card-top">
        <span class="card-cat">${c.label}</span>
        <span class="card-level" aria-hidden="true"><i class="fa-solid fa-circle-play"></i></span>
      </div>
      <div class="card-body">
        <h3>${c.title}</h3>
        <p>${c.desc}</p>
        <div class="card-foot">
          <span><span class="sr-only">Vídeo · </span>${c.time} de vídeo</span>
          <span class="go">Assistir ${arrowIcon()}</span>
        </div>
      </div>`;

    card.addEventListener('click', () => openVideoModal(c));
    card.addEventListener('keydown', (e) => {
      // Só reage a Enter/Espaço quando o foco está no card em si, não no botão de favoritar
      if((e.key === 'Enter' || e.key === ' ') && e.target === card){
        e.preventDefault();
        openVideoModal(c);
      }
    });

    const favBtn = card.querySelector('.fav-toggle');
    favBtn.addEventListener('click', (e) => {
      e.stopPropagation(); // não deixa o clique "vazar" pro card e abrir o vídeo
      toggleFavorite(c.id);
      const nowFav = favorites.ids.includes(c.id);
      favBtn.classList.toggle('is-fav', nowFav);
      favBtn.setAttribute('aria-pressed', nowFav);
      favBtn.setAttribute('aria-label', nowFav ? 'Remover dos favoritos' : 'Adicionar aos favoritos');
    });

    return card;
  }

  function toggleFavorite(courseId){
    const idx = favorites.ids.indexOf(courseId);
    if(idx === -1){
      favorites.ids.push(courseId);
    } else {
      favorites.ids.splice(idx, 1);
    }
    saveFavorites(favorites);
    updateFavCount();
    // Se a tela de favoritos estiver aberta agora, atualiza a lista na hora
    if(!areaModalEl.hidden && areaModalEyebrowEl.textContent === 'Seus favoritos'){
      currentAreaResults = courses.filter(c => favorites.ids.includes(c.id));
      refreshCourseScreen();
    }
  }

  // ================================================================
  // ---------- Tela cheia de tutoriais (área específica OU busca) ----------
  // ================================================================
  // Os tutoriais agora vivem só dentro dos cards de área — não existe mais
  // uma lista solta na página. Essa mesma tela é reaproveitada pela busca,
  // já que as duas coisas são "mostrar uma lista de tutoriais em tela cheia".
  const areaModalEl = document.getElementById('areaModal');
  const areaModalTitleEl = document.getElementById('areaModalTitle');
  const areaModalEyebrowEl = document.getElementById('areaModalEyebrow');
  const areaCourseGridEl = document.getElementById('areaCourseGrid');
  const areaNoResultsEl = document.getElementById('areaNoResults');
  const areaModalCloseBtn = document.getElementById('areaModalClose');
  let areaModalLastFocusedEl = null;
  let currentAreaResults = []; // guarda a lista atual pra poder re-renderizar (ex: depois de responder o quiz)
  let currentEmptyMessage = '';

  function openCourseScreen({ eyebrow, title, results, emptyMessage }){
    areaModalLastFocusedEl = document.activeElement;
    currentAreaResults = results;
    currentEmptyMessage = emptyMessage || 'Ainda não há tutoriais publicados nessa área.';

    areaModalEyebrowEl.textContent = eyebrow;
    areaModalTitleEl.textContent = title;

    areaCourseGridEl.innerHTML = '';
    results.forEach(c => areaCourseGridEl.appendChild(buildCourseCard(c)));
    areaNoResultsEl.hidden = results.length > 0;
    areaNoResultsEl.textContent = currentEmptyMessage;

    areaModalEl.hidden = false;
    lockBodyScroll();
    // Empilha um estado no histórico do navegador — assim a setinha de
    // "voltar" fecha essa tela em vez de sair do site.
    history.pushState({ mseOverlay: 'area' }, '', '');
    // setTimeout(0) evita um bug real: se isso rodar no mesmo ciclo da
    // tecla Enter (ex: buscar e apertar Enter), o navegador entende que
    // "Enter" clicou no botão recém-focado e fecha a tela na mesma hora.
    setTimeout(() => areaModalCloseBtn.focus(), 0);
  }

  function refreshCourseScreen(){
    areaCourseGridEl.innerHTML = '';
    currentAreaResults.forEach(c => areaCourseGridEl.appendChild(buildCourseCard(c)));
    areaNoResultsEl.hidden = currentAreaResults.length > 0;
  }

  function openAreaCourses(slug){
    const cardBtn = document.querySelector(`.area-card[data-area="${slug}"]`);
    const areaName = cardBtn ? cardBtn.querySelector('h4').textContent : 'Área';
    openCourseScreen({
      eyebrow: 'Tutoriais da área',
      title: areaName,
      results: courses.filter(c => c.cat === slug)
    });
  }

  function openSearchResults(query){
    const q = query.toLowerCase();
    const results = courses.filter(c => (c.title + ' ' + c.desc + ' ' + c.label).toLowerCase().includes(q));
    openCourseScreen({
      eyebrow: 'Resultados da busca',
      title: `"${query}"`,
      results
    });
  }

  function openFavorites(){
    openCourseScreen({
      eyebrow: 'Seus favoritos',
      title: 'Tutoriais salvos',
      results: courses.filter(c => favorites.ids.includes(c.id)),
      emptyMessage: 'Você ainda não salvou nenhum tutorial. Clique na estrela de um card pra guardar aqui.'
    });
  }
  document.getElementById('favViewBtn').addEventListener('click', openFavorites);
  updateFavCount();

  // Esconde a tela (não mexe no histórico — quem decide "voltar" é o
  // navegador via popstate, ou o botão X chamando history.back()).
  function closeAreaCourses(){
    if(areaModalEl.hidden) return;
    areaModalEl.hidden = true;
    unlockBodyScroll();
    if(areaModalLastFocusedEl) areaModalLastFocusedEl.focus();
  }

  document.querySelector('.areas-grid').addEventListener('click', (e) => {
    const card = e.target.closest('.area-card');
    if(!card) return;
    openAreaCourses(card.dataset.area);
  });

  renderProgressPanel();

  // O botão de fechar e o Esc só pedem pro navegador voltar — quem
  // efetivamente esconde a tela é sempre o listener de popstate mais abaixo.
  areaModalCloseBtn.addEventListener('click', () => history.back());
  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape' && !areaModalEl.hidden) history.back();
  });

  // ================================================================
  // ---------- Modal de vídeo do catálogo (player + quiz de saída) ----------
  // ================================================================
  const videoModalEl = document.getElementById('videoModal');
  const videoModalTitleEl = document.getElementById('videoModalTitle');
  const videoModalAreaEl = document.getElementById('videoModalArea');
  const videoModalBodyEl = document.getElementById('videoModalBody');
  const videoModalCloseBtn = document.getElementById('videoModalClose');

  const modalState = {
    course: null,
    player: null,
    quizAnswered: false,
    lastFocusedEl: null,
    travaTimer: null, // vigia a posição do vídeo do YouTube na 1ª vez
  };

  function openVideoModal(course){
    modalState.course = course;
    modalState.player = null;
    modalState.quizAnswered = false;
    modalState.lastFocusedEl = document.activeElement;

    videoModalTitleEl.textContent = course.title;
    videoModalAreaEl.textContent = course.label;
    videoModalBodyEl.innerHTML = `
      <div class="video-modal-inner">
        <div class="vid-player-wrap">
          <div id="catalog-yt-player"></div>
        </div>
        <div id="catalogBonusQuiz"></div>
      </div>
    `;

    videoModalEl.hidden = false;
    lockBodyScroll();
    // Empilha um estado no histórico — a setinha de "voltar" do navegador
    // fecha o vídeo primeiro, sem sair da tela de área que estiver por baixo.
    history.pushState({ mseOverlay: 'video' }, '', '');

    // O detalhe traz o vídeo e a pergunta do banco. Curso do catálogo
    // pode ser do YouTube (id) ou um arquivo no S3 (URL assinada), então
    // o player muda conforme o caso.
    carregarDetalheCurso(course.id).then(detalhe => {
      course.questions = detalhe.questions || [];
      const wrap = videoModalBodyEl.querySelector('.vid-player-wrap');
      if(!wrap) return;

      // Playlist também pode ser cadastrada no catálogo, não só na
      // trilha. Sem este caso o id da playlist era tratado como id de
      // vídeo e o player abria quebrado.
      if(detalhe.video_source === 'playlist'){
        wrap.innerHTML = `<iframe src="https://www.youtube-nocookie.com/embed/videoseries?list=${encodeURIComponent(detalhe.youtube_id)}&rel=0"
          title="${course.title}" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen
          style="width:100%;height:100%;border:0"></iframe>`;
        return;
      }

      // Na primeira vez a pessoa não pode adiantar o vídeo; depois de já
      // ter concluído, pode pular à vontade — quem volta pra rever um
      // trecho não deveria ter que assistir tudo de novo.
      const jaConcluiu = catalogProgress.completed.includes(course.id);
      const trava = criarTravaDeAvanco(jaConcluiu);

      if(detalhe.video_url){
        wrap.innerHTML = `<video src="${detalhe.video_url}" controls playsinline style="width:100%;border-radius:12px"></video>`;
        const v = wrap.querySelector('video');

        // Os controles nativos ficam visíveis mesmo travado, pra dar pra
        // VOLTAR. O que é bloqueado é só avançar além do que já foi visto —
        // e aqui dá pra bloquear com precisão porque o <video>, ao
        // contrário do YouTube, avisa quando alguém mexe na barra.
        const travarBusca = () => {
          trava.definirDuracao(v.duration);
          const voltarPara = trava.aoBuscar(v.currentTime);
          if(voltarPara !== null) v.currentTime = voltarPara;
        };
        v.addEventListener('seeking', travarBusca);
        v.addEventListener('seeked', travarBusca);

        v.addEventListener('timeupdate', () => {
          if(!v.duration) return;
          trava.definirDuracao(v.duration);
          const voltarPara = trava.conferir(v.currentTime);
          if(voltarPara !== null) v.currentTime = voltarPara;
          enviarProgresso(course.id, trava.pct);
        });
        v.addEventListener('ended', () => { enviarProgresso(course.id, 1); showBonusQuiz(); });
        return;
      }

      if(!detalhe.youtube_id){
        wrap.innerHTML = '<div class="vid-hint"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Esta aula ainda não tem vídeo cadastrado.</div>';
        return;
      }

      // Quem já concluiu vê o player do YouTube inteiro e pula à vontade.
      // Na primeira vez a barra do YouTube não aparece — era por ela que
      // dava pra adiantar. No lugar entram os controles da Academy: uma
      // barra que só aceita clique no trecho já assistido e um "voltar
      // 10s". Voltar continua possível; avançar deixou de existir.
      //
      // Por que não deixar a barra do YouTube e só corrigir a posição:
      // o player do YouTube não avisa quando alguém mexe na barra, só dá
      // pra perguntar onde ele está de tempos em tempos. Com a barra à
      // mostra, todo pulo aparecia por um instante antes de voltar — trava
      // que pisca não é trava.
      if(!jaConcluiu){
        wrap.insertAdjacentHTML('beforeend', marcacaoControlesDeRevisao('catalog'));
      }

      loadYouTubeApi().then(() => {
        modalState.player = new YT.Player('catalog-yt-player', {
          videoId: detalhe.youtube_id,
          playerVars: jaConcluiu
            ? { controls: 1, modestbranding: 1, rel: 0, fs: 1 }
            // fs:0 tira o botão de tela cheia DO YOUTUBE: em tela cheia do
            // player o YouTube mostra a barra dele de volta, e aí controls:0
            // deixaria de valer.
            : { controls: 0, disablekb: 1, modestbranding: 1, rel: 0, fs: 0 },
          events: {
            onReady: () => {
              if(jaConcluiu) return;
              const p = modalState.player;
              ligarControlesDeRevisao(
                wrap,
                trava,
                (seg) => p.seekTo(seg, true),
                () => (typeof p.getCurrentTime === 'function' ? p.getCurrentTime() : 0)
              );

              // Rede de segurança pro que escapar dos controles próprios
              // (atalho de teclado, clique no player). Meio em meio segundo,
              // igual à trilha.
              clearInterval(modalState.travaTimer);
              modalState.travaTimer = setInterval(() => {
                const pl = modalState.player;
                if(!pl || typeof pl.getDuration !== 'function') return;
                const total = pl.getDuration();
                if(total <= 0) return;
                trava.definirDuracao(total);
                const voltarPara = trava.conferir(pl.getCurrentTime());
                if(voltarPara !== null) pl.seekTo(voltarPara, true);
                atualizarBarraDeRevisao('catalog', trava);
                enviarProgresso(course.id, trava.pct);
              }, 500);
            },
            onStateChange: (e) => {
              if(e.data === YT.PlayerState.ENDED){ enviarProgresso(course.id, 1); showBonusQuiz(); }
            }
          }
        });
      });
    }).catch(e => {
      const wrap = videoModalBodyEl.querySelector('.vid-player-wrap');
      if(wrap) wrap.innerHTML = `<div class="vid-hint"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Não foi possível carregar: ${e.message}</div>`;
    });

    // mesmo motivo do outro setTimeout: evita que a tecla Enter que abriu
    // o vídeo também "clique" no botão de fechar por engano
    setTimeout(() => videoModalCloseBtn.focus(), 0);
  }

  // Pergunta bônus opcional: aparece quando o vídeo termina naturalmente,
  // mas nunca impede fechar — é só um extra pra quem quiser ganhar pontos.
  function showBonusQuiz(){
    const course = modalState.course;
    if(modalState.quizAnswered || catalogProgress.completed.includes(course.id)) return;

    const container = document.getElementById('catalogBonusQuiz');
    if(!container || container.querySelector('.quiz-box')) return;

    const question = (course.questions || [])[0];
    if(!question) return; // aula sem pergunta cadastrada
    container.innerHTML = `
      <div class="quiz-box">
        <h5>Pergunta rápida (opcional, vale +10 pontos): ${question.question_text}</h5>
        <div class="quiz-options">
          ${question.options.map(opt => `
            <button type="button" class="quiz-option" data-option-id="${opt.id}">
              <span>${opt.option_text}</span>
            </button>
          `).join('')}
        </div>
        <div class="quiz-feedback" id="catalogQuizFeedback" hidden></div>
      </div>
    `;

    container.querySelectorAll('.quiz-option').forEach(btn => {
      btn.addEventListener('click', () => answerCatalogQuiz(question, btn));
    });
  }

  async function answerCatalogQuiz(question, btn){
    const allOptions = document.querySelectorAll('#catalogBonusQuiz .quiz-option');
    const feedbackEl = document.getElementById('catalogQuizFeedback');

    allOptions.forEach(o => o.disabled = true);

    // Mesma regra da trilha: quem confere a resposta e credita os pontos
    // é o servidor, não o navegador.
    let isCorrect;
    try {
      const r = await apiPost('api/quiz/submit.php', {
        question_id: question.id,
        option_id: parseInt(btn.dataset.optionId, 10),
      });
      isCorrect = !!r.correct;
    } catch(e){
      feedbackEl.hidden = false;
      feedbackEl.className = 'quiz-feedback bad';
      feedbackEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> ${e.message}`;
      allOptions.forEach(o => o.disabled = false);
      return;
    }

    if(isCorrect){
      btn.classList.add('correct');
      btn.insertAdjacentHTML('beforeend', '<i class="fa-solid fa-check opt-icon" aria-hidden="true"></i>');
      feedbackEl.hidden = false;
      feedbackEl.className = 'quiz-feedback ok';
      feedbackEl.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Isso aí — sua bússola está calibrada. +10 pontos.';

      modalState.quizAnswered = true;
      const course = modalState.course;
      if(!catalogProgress.completed.includes(course.id)){
        catalogProgress.completed.push(course.id);
        catalogProgress.points += 10;
        saveCatalogProgress(catalogProgress);
      }
      // Atualiza o selo "Concluído" na tela de área/busca que ficou aberta por baixo
      if(!areaModalEl.hidden) refreshCourseScreen();
      renderProgressPanel();
    } else {
      btn.classList.add('incorrect');
      btn.insertAdjacentHTML('beforeend', '<i class="fa-solid fa-circle-xmark opt-icon" aria-hidden="true"></i>');
      feedbackEl.hidden = false;
      feedbackEl.className = 'quiz-feedback bad';
      feedbackEl.innerHTML = '<i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> Quase lá — dá mais uma olhada e tenta de novo.';

      setTimeout(() => {
        allOptions.forEach(o => {
          o.disabled = false;
          o.classList.remove('incorrect');
        });
        feedbackEl.hidden = true;
      }, 1200);
    }
  }

  function closeVideoModal(){
    if(videoModalEl.hidden) return;
    clearInterval(modalState.travaTimer); // senão segue rodando sobre um player destruído
    modalState.travaTimer = null;
    if(modalState.player && typeof modalState.player.destroy === 'function'){
      modalState.player.destroy();
    }
    modalState.player = null;
    videoModalEl.hidden = true;
    videoModalBodyEl.innerHTML = '';
    unlockBodyScroll();
    if(modalState.lastFocusedEl) modalState.lastFocusedEl.focus();
  }

  // O botão de fechar e o Esc só pedem pro navegador voltar — quem esconde
  // de fato o vídeo é sempre o listener de popstate (mantém tudo em sincronia
  // com a setinha de "voltar" do navegador, que faz a mesma coisa).
  videoModalCloseBtn.addEventListener('click', () => history.back());
  document.addEventListener('keydown', (e) => {
    if(videoModalEl.hidden) return;
    if(e.key === 'Escape'){
      history.back();
      return;
    }
    if(e.key === 'Tab'){
      // Prende o foco do teclado dentro do modal enquanto ele estiver aberto
      const focusables = videoModalEl.querySelectorAll('button:not([disabled]), [href], input, [tabindex]:not([tabindex="-1"])');
      if(focusables.length === 0) return;
      const first = focusables[0];
      const last = focusables[focusables.length - 1];
      if(e.shiftKey && document.activeElement === first){
        e.preventDefault();
        last.focus();
      } else if(!e.shiftKey && document.activeElement === last){
        e.preventDefault();
        first.focus();
      }
    }
  });

  // ---------- popstate: única fonte de verdade pra fechar as telas cheias ----------
  // Dispara tanto quando a pessoa usa a setinha "voltar" do navegador quanto
  // quando o botão X chama history.back() — os dois casos passam por aqui.
  window.addEventListener('popstate', (e) => {
    const state = e.state || {};
    if(state.mseOverlay !== 'video') closeVideoModal();
    if(state.mseOverlay !== 'area') closeAreaCourses();
  });

  // ---------- Busca (header + card do hero) ----------
  function runSearch(query){
    query = (query || '').trim();
    if(!query) return;

    showView('cursos');
    openSearchResults(query);
  }

  document.getElementById('headerSearch').addEventListener('keydown', (e) => {
    if(e.key === 'Enter') runSearch(e.target.value);
  });
  document.getElementById('heroSearch').addEventListener('keydown', (e) => {
    if(e.key === 'Enter') runSearch(e.target.value);
  });

  // ---------- Contadores animados ----------
  const counters = document.querySelectorAll('.num[data-count]');
  const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if(entry.isIntersecting){
        const el = entry.target;
        const target = parseInt(el.dataset.count, 10);
        let current = 0;
        const step = Math.max(1, Math.round(target / 60));
        const tick = () => {
          current += step;
          if(current >= target){ el.textContent = target; return; }
          el.textContent = current;
          requestAnimationFrame(tick);
        };
        tick();
        statsObserver.unobserve(el);
      }
    });
  }, {threshold:0.4});
  counters.forEach(c => statsObserver.observe(c));

// ============================================================
// Conexão REAL com a API — só pra detectar login/admin e o botão
// "Adicionar pessoas". O resto do site continua em localStorage
// (isso aqui é o primeiro pedaço conectado de verdade ao backend).
// ============================================================
(function initRealAdminIntegration(){
  const REAL_SESSION_KEY = 'mse_academy_real_session_token';

  function getRealSessionToken(){
    return localStorage.getItem(REAL_SESSION_KEY);
  }
  function setRealSessionToken(token){
    localStorage.setItem(REAL_SESSION_KEY, token);
  }
  function clearRealSession(){
    localStorage.removeItem(REAL_SESSION_KEY);
    localStorage.removeItem('mse_academy_real_user_name');
  }

  async function apiFetch(path, options = {}){
    const token = getRealSessionToken();
    const headers = Object.assign({}, options.headers, {
      'Content-Type': 'application/json',
    });
    if(token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(path, Object.assign({
      credentials: 'include', // manda cookies mesmo se a Academy estiver
                               // rodando num contexto diferente (iframe,
                               // por exemplo) — sem isso, a leitura da
                               // sessão do Portal (portal_session.php)
                               // nunca recebe o cookie de sessão.
    }, options, { headers }));
    const data = await res.json().catch(() => ({}));
    if(!res.ok){
      const err = new Error(data.error || 'Erro na API');
      err.status = res.status;
      throw err;
    }
    return data;
  }

  // 0) Tenta ler a sessão do Portal direto (só funciona no mesmo
  //    domínio) — usado só como RESERVA, depois de checar o token.
  async function tryPortalSessionLogin(diagnostico){
    try{
      const data = await apiFetch('api/auth/portal_session.php');
      setRealSessionToken(data.token);
      if(data.user && data.user.first_name){
        localStorage.setItem('mse_academy_real_user_name', data.user.first_name);
      }
      diagnostico.tentativas.push({ metodo: 'sessão do Portal', resultado: 'sucesso', usuario: data.user });
      return true;
    }catch(e){
      diagnostico.tentativas.push({ metodo: 'sessão do Portal', resultado: 'falhou', erro: e.message, status: e.status });
      return false;
    }
  }

  // 1) MÉTODO PRINCIPAL: token assinado (?sso=...) na URL — é o que o
  //    Portal deve gerar e mandar quando a pessoa clica em MSE Academy.
  //    Localmente, usamos scripts/generate_test_login.php pra gerar
  //    esse link de teste.
  async function tryRealSsoLogin(diagnostico){
    const params = new URLSearchParams(window.location.search);
    const ssoToken = params.get('sso');
    diagnostico.temSsoNaUrl = !!ssoToken;

    if(ssoToken){
      // Remove credencial da barra/histórico antes de qualquer chamada.
      params.delete('sso');
      const clean = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
      window.history.replaceState({}, '', clean);
      clearRealSession();

      try{
        const data = await apiFetch('api/auth/sso.php', {
          method: 'POST',
          body: JSON.stringify({ token: ssoToken }),
        });
        setRealSessionToken(data.token);
        if(data.user && data.user.first_name){
          localStorage.setItem('mse_academy_real_user_name', data.user.first_name);
        }
        diagnostico.tentativas.push({ metodo: 'token assinado (?sso=)', resultado: 'sucesso', usuario: data.user });
      }catch(e){
        diagnostico.tentativas.push({ metodo: 'token assinado (?sso=)', resultado: 'falhou', erro: e.message, status: e.status });
        console.warn('Login SSO real falhou:', e.message);
      }
      return; // achou ?sso= — não tenta mais nada depois disso
    }

    // Sem token novo, preserva sessão Academy já existente. A sessão PHP
    // compartilhada continua disponível apenas como fallback controlado.
    if(getRealSessionToken()) return;
    await tryPortalSessionLogin(diagnostico);
  }

  // Mostra um painel BEM discreto no canto da tela com o que aconteceu
  // no login — só pra facilitar diagnóstico sem precisar de F12. Fica
  // sempre visível de propósito (é só texto pequeno, num canto) —
  // depois que confirmar que está tudo funcionando, dá pra remover essa
  // função e a chamada dela no DOMContentLoaded.
  function mostrarPainelDiagnostico(diagnostico){
    const caixa = document.createElement('div');
    caixa.style.cssText = 'position:fixed; bottom:8px; right:8px; z-index:9999; background:#1c1b1a; color:#fff; font:11px monospace; padding:10px 14px; border-radius:8px; max-width:340px; opacity:0.92; max-height:250px; overflow:auto;';
    let html = '<b>Diagnóstico de login</b><br>';
    html += 'URL: ' + diagnostico.url.slice(0, 60) + '<br>';
    html += 'Tinha ?sso= na URL: ' + diagnostico.temSsoNaUrl + '<br>';
    html += 'Token salvo no fim: ' + diagnostico.temTokenSalvo + '<br>';
    html += 'É admin: ' + diagnostico.isAdmin + '<br><br>';
    diagnostico.tentativas.forEach(t => {
      html += '<b>' + t.metodo + '</b>: ' + t.resultado;
      if(t.resultado === 'falhou') html += ' (status ' + t.status + ': ' + t.erro + ')';
      html += '<br>';
    });
    caixa.innerHTML = html;
    const btnFechar = document.createElement('div');
    btnFechar.textContent = '✕ fechar';
    btnFechar.style.cssText = 'text-align:right; cursor:pointer; margin-top:6px; opacity:0.7;';
    btnFechar.onclick = () => caixa.remove();
    caixa.appendChild(btnFechar);
    document.body.appendChild(caixa);
  }

  // 2) Confere se a pessoa logada É admin de verdade (perguntando pra
  //    API, nunca confiando em nada guardado só no navegador).
  async function checkIsRealAdmin(){
    const token = getRealSessionToken();
    if(!token) return false;
    try{
      const data = await apiFetch('api/auth/me.php');
      return data.user && data.user.role === 'admin';
    }catch(e){
      return false;
    }
  }

  function openAdminModal(){
    document.getElementById('adminModalOverlay').hidden = false;
    document.getElementById('adminModalEmail').value = '';
    document.getElementById('adminModalEmail').focus();
    const feedback = document.getElementById('adminModalFeedback');
    feedback.hidden = true;
  }
  function closeAdminModal(){
    document.getElementById('adminModalOverlay').hidden = true;
  }

  async function submitAddPerson(){
    const email = document.getElementById('adminModalEmail').value.trim();
    const feedback = document.getElementById('adminModalFeedback');
    const submitBtn = document.getElementById('adminModalSubmit');

    if(!email || !email.includes('@')){
      feedback.hidden = false;
      feedback.className = 'admin-modal-feedback erro';
      feedback.textContent = 'Digite um e-mail válido.';
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Adicionando...';

    try{
      const data = await apiFetch('api/admin/manage_admins.php', {
        method: 'POST',
        body: JSON.stringify({ email, action: 'promote' }),
      });
      feedback.hidden = false;
      feedback.className = 'admin-modal-feedback ok';
      feedback.textContent = data.message || (email + ' agora é admin — vai poder adicionar vídeos e outras pessoas também, assim que ela acessar a Academy pela primeira vez.');
    }catch(e){
      feedback.hidden = false;
      feedback.className = 'admin-modal-feedback erro';
      feedback.textContent = e.message || 'Não foi possível adicionar essa pessoa.';
    }finally{
      submitBtn.disabled = false;
      submitBtn.textContent = 'Adicionar';
    }
  }

  // ---------- Modal de adicionar vídeo ----------
  // As áreas vêm do banco, não de uma lista fixa aqui — assim qualquer
  // área cadastrada aparece sozinha, sem precisar mexer no JavaScript.
  async function fillAreaSelect(){
    const select = document.getElementById('adminVideoModalArea');
    if(!select || select.options.length) return; // já preenchido
    try{
      const data = await apiFetch('api/areas/list.php');
      data.areas.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a.slug;
        opt.textContent = a.name;
        select.appendChild(opt);
      });
    }catch(e){
      // Sem isso o seletor ficaria vazio sem explicação nenhuma.
      const opt = document.createElement('option');
      opt.textContent = 'Não consegui carregar as áreas: ' + e.message;
      opt.disabled = true;
      select.appendChild(opt);
    }
  }

  // O tipo vem marcado como "Curso (catálogo)" por ser a primeira opção,
  // e quem quer publicar na trilha costuma não reparar nisso — a aula
  // some pro catálogo e a pessoa fica procurando na trilha. Este aviso
  // diz, em uma linha, onde ela vai aparecer.
  function atualizarOndeAparece(){
    const alvo = document.getElementById('videoModalOndeAparece');
    if(!alvo) return;
    const type = document.getElementById('videoModalType').value;
    alvo.innerHTML = type === 'onboarding'
      ? 'Vai aparecer na <strong>trilha de Integração</strong>, que todo colaborador percorre.'
      : 'Vai aparecer no <strong>catálogo</strong>, dentro da área escolhida — <strong>não</strong> na trilha de Integração.';
  }

  function atualizarVisibilidadeArea(){
    atualizarOndeAparece();
    const type = document.getElementById('videoModalType').value;
    const areaWrap = document.getElementById('adminVideoModalAreaWrap');
    const hint = document.getElementById('videoModalHint');
    if(type === 'onboarding'){
      areaWrap.style.display = 'none';
      hint.textContent = 'Vídeo de integração — aparece igual pra todo mundo, não importa a área da pessoa.';
    } else {
      areaWrap.style.display = '';
      hint.textContent = 'O curso já fica disponível pra quem tiver acesso àquela área assim que salvar.';
    }
  }

  function atualizarVisibilidadeOrigemVideo(){
    const origem = document.getElementById('videoModalOrigem').value;
    document.getElementById('videoModalYoutubeWrap').hidden = origem !== 'youtube';
    document.getElementById('videoModalArquivoWrap').hidden = origem !== 's3';
    document.getElementById('videoModalPlaylistWrap').hidden = origem !== 'playlist';
    // Sortear entre playlists não faz sentido: elas são conteúdo extra,
    // não alternativas de uma mesma aula obrigatória.
    document.getElementById('videoModalSorteioWrap').hidden = origem === 'playlist';
  }

  // O id da playlist é o trecho depois de "list=". É mais longo e
  // variável que o de vídeo (começa com PL, UU, OL...), por isso não
  // serve a mesma regra de 11 caracteres.
  function extrairPlaylistId(entrada){
    entrada = (entrada || '').trim();
    if(/^[A-Za-z0-9_-]{12,64}$/.test(entrada)) return entrada; // já é só o id
    const m = entrada.match(/[?&]list=([A-Za-z0-9_-]{12,64})/);
    return m ? m[1] : null;
  }

  // Aceita tanto o link inteiro do YouTube (várias formas: youtube.com/watch?v=,
  // youtu.be/, /embed/) quanto o ID puro de 11 caracteres, já digitado direto.
  function extrairYoutubeId(entrada){
    entrada = entrada.trim();
    if(/^[A-Za-z0-9_-]{11}$/.test(entrada)) return entrada; // já é só o ID
    const padroes = [
      /(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{11})/,
    ];
    for(const padrao of padroes){
      const m = entrada.match(padrao);
      if(m) return m[1];
    }
    return null;
  }

  function openVideoModal(){
    fillAreaSelect();
    document.getElementById('videoModalOverlay').hidden = false;
    document.getElementById('videoModalFeedback').hidden = true;
    atualizarVisibilidadeArea();
    atualizarVisibilidadeOrigemVideo();
  }
  function closeVideoModal(){
    document.getElementById('videoModalOverlay').hidden = true;
  }

  async function submitAddVideo(){
    const type = document.getElementById('videoModalType').value;
    const areaSlug = type === 'onboarding' ? '' : document.getElementById('adminVideoModalArea').value;
    const titulo = document.getElementById('videoModalTitulo').value.trim();
    const descricao = document.getElementById('videoModalDescricao').value.trim();
    const duracao = parseInt(document.getElementById('videoModalDuracao').value, 10) || 5;
    const origem = document.getElementById('videoModalOrigem').value;
    const arquivo = origem === 's3' ? document.getElementById('videoModalArquivo').files[0] : null;
    const youtubeEntrada = document.getElementById('videoModalYoutubeUrl').value.trim();
    const playlistEntrada = document.getElementById('videoModalPlaylistUrl').value.trim();
    const grupoSorteio = document.getElementById('videoModalSorteio').value.trim();
    const pergunta = document.getElementById('videoModalPergunta').value.trim();
    const feedback = document.getElementById('videoModalFeedback');
    const submitBtn = document.getElementById('videoModalSubmit');

    if(!titulo){
      feedback.hidden = false;
      feedback.className = 'admin-modal-feedback erro';
      feedback.textContent = 'Preencha o título.';
      return;
    }
    if(origem === 's3' && !arquivo){
      feedback.hidden = false;
      feedback.className = 'admin-modal-feedback erro';
      feedback.textContent = 'Escolha um arquivo de vídeo.';
      return;
    }
    // Conferido aqui, antes de enfileirar: se a área faltar, o erro só
    // apareceria ao criar o curso — depois do vídeo inteiro já ter
    // subido, jogando fora os minutos de envio.
    if(type !== 'onboarding' && !areaSlug){
      feedback.hidden = false;
      feedback.className = 'admin-modal-feedback erro';
      feedback.textContent = 'Escolha a área do curso.';
      return;
    }
    let youtubeId = null;
    if(origem === 'youtube'){
      youtubeId = extrairYoutubeId(youtubeEntrada);
      if(!youtubeId){
        // Link de playlist colado no campo de vídeo é o engano mais
        // provável aqui — a mensagem antiga só dizia "não consegui
        // identificar o vídeo", sem indicar que existe a opção certa
        // logo acima.
        const idPlaylist = extrairPlaylistId(youtubeEntrada);
        if(idPlaylist){
          document.getElementById('videoModalOrigem').value = 'playlist';
          document.getElementById('videoModalPlaylistUrl').value = youtubeEntrada;
          document.getElementById('videoModalYoutubeUrl').value = '';
          atualizarVisibilidadeOrigemVideo();
          feedback.hidden = false;
          feedback.className = 'admin-modal-feedback ok';
          feedback.textContent = 'Esse link é de uma playlist, não de um vídeo. Já troquei para "Playlist do YouTube" e movi o link pro campo certo — confira e clique em Adicionar de novo.';
          return;
        }
        feedback.hidden = false;
        feedback.className = 'admin-modal-feedback erro';
        feedback.textContent = 'Não consegui identificar o vídeo nesse link do YouTube. Cola o link completo (ex: https://youtube.com/watch?v=...) ou só o código de 11 caracteres. Se for uma playlist, escolha "Playlist do YouTube" em "De onde vem o vídeo?".';
        return;
      }
    }
    if(origem === 'playlist'){
      youtubeId = extrairPlaylistId(playlistEntrada);
      if(!youtubeId){
        feedback.hidden = false;
        feedback.className = 'admin-modal-feedback erro';
        feedback.textContent = 'Não consegui identificar a playlist nesse link. Cola o link completo (ex: https://youtube.com/playlist?list=PL...) ou só o código depois de "list=".';
        return;
      }
    }

    submitBtn.disabled = true;
    feedback.hidden = true;

    try{
      const body = {
        area_slug: areaSlug,
        type,
        title: titulo,
        description: descricao,
        duration_minutes: duracao,
      };

      if(origem === 'youtube'){
        body.video_source = 'youtube';
        body.youtube_id = youtubeId;
      }
      if(origem === 'playlist'){
        body.video_source = 'playlist';
        body.youtube_id = youtubeId; // aqui é o id da playlist, não de um vídeo
      }
      if(grupoSorteio && origem !== 'playlist'){
        body.grupo_sorteio = grupoSorteio;
      }

      if(pergunta){
        body.quiz_question = pergunta;
        body.quiz_options = [0, 1, 2].map(i => ({
          text: document.getElementById('videoModalOpcao' + i).value.trim(),
          is_correct: document.querySelector(`input[name="videoModalCorreta"][value="${i}"]`).checked,
        })).filter(o => o.text);
      }

      if(origem === 's3'){
        // Vai pra fila em vez de travar o modal: o envio continua no
        // painel e o formulário já fica livre pro próximo vídeo.
        enfileirarUpload(arquivo, body, titulo);
        feedback.hidden = false;
        feedback.className = 'admin-modal-feedback ok';
        feedback.textContent = `"${titulo}" entrou na fila de envio. Pode fechar esta janela e adicionar outro — o envio continua no painel do canto.`;
        document.getElementById('videoModalTitulo').value = '';
        document.getElementById('videoModalDescricao').value = '';
        document.getElementById('videoModalArquivo').value = '';
        document.getElementById('videoModalPergunta').value = '';
        [0, 1, 2].forEach(i => { document.getElementById('videoModalOpcao' + i).value = ''; });
      } else {
        await apiFetch('api/admin/courses/create.php', {
          method: 'POST',
          body: JSON.stringify(body),
        });
        feedback.hidden = false;
        feedback.className = 'admin-modal-feedback ok';
        feedback.textContent = `Vídeo "${titulo}" adicionado com sucesso!`;
      }
    }catch(e){
      feedback.hidden = false;
      feedback.className = 'admin-modal-feedback erro';
      feedback.textContent = e.message || 'Não foi possível adicionar o vídeo.';
    }finally{
      submitBtn.disabled = false;
      submitBtn.textContent = 'Adicionar vídeo';
    }
  }

  // ---------- Fila de envio de vídeos ----------
  // O envio roda aqui fora do modal de propósito: fechar o modal não
  // interrompe nada, e dá pra enfileirar vários vídeos seguidos. O que
  // NÃO dá é fechar a aba — aí o navegador para de mandar os bytes.
  const filaUploads = [];
  let uploadEmAndamento = false;


  function formatarMB(bytes){
    return (bytes / 1048576).toFixed(0) + ' MB';
  }

  function formatarTempo(segundos){
    if(!isFinite(segundos) || segundos < 0) return '';
    if(segundos < 60) return Math.round(segundos) + 's restantes';
    return Math.round(segundos / 60) + ' min restantes';
  }

  function renderPainelUploads(){
    const painel = document.getElementById('uploadsPainel');
    const lista = document.getElementById('uploadsPainelLista');
    if(!painel || !lista) return;

    if(!filaUploads.length){ painel.hidden = true; return; }
    painel.hidden = false;

    const ativos = filaUploads.filter(j => j.status === 'enviando' || j.status === 'aguardando').length;
    document.getElementById('uploadsPainelTitulo').textContent =
      ativos ? `Enviando vídeos (${ativos})` : 'Envios concluídos';

    lista.innerHTML = '';
    filaUploads.forEach(job => {
      const item = document.createElement('div');
      item.className = 'uploads-item estado-' + job.status;

      let detalhe = '';
      if(job.status === 'aguardando') detalhe = 'na fila';
      else if(job.status === 'enviando') detalhe = `${job.pct}% · ${formatarMB(job.enviado)} de ${formatarMB(job.total)} · ${formatarTempo(job.restante)}`;
      else if(job.status === 'concluido') detalhe = 'concluído';
      else if(job.status === 'cancelado') detalhe = 'cancelado';
      else if(job.status === 'erro') detalhe = job.erro || 'falhou';

      item.innerHTML = `
        <div class="uploads-item-linha">
          <span class="uploads-item-nome">${job.titulo}</span>
          ${job.status === 'enviando' || job.status === 'aguardando'
            ? '<button type="button" class="uploads-item-cancelar" aria-label="Cancelar">&times;</button>'
            : '<button type="button" class="uploads-item-cancelar" aria-label="Remover da lista">&times;</button>'}
        </div>
        <div class="uploads-item-barra"><div class="uploads-item-barra-fill" style="width:${job.pct || 0}%"></div></div>
        <div class="uploads-item-detalhe">${detalhe}</div>
      `;
      item.querySelector('.uploads-item-cancelar').addEventListener('click', () => {
        if(job.status === 'enviando' || job.status === 'aguardando'){
          job.cancelar = true;
          job.status = 'cancelado';
        } else {
          const i = filaUploads.indexOf(job);
          if(i >= 0) filaUploads.splice(i, 1);
        }
        renderPainelUploads();
      });
      lista.appendChild(item);
    });
  }

  // Manda o arquivo em pedaços de ~5MB. Cada pedaço é uma requisição
  // pequena e independente — é isso que permite arquivo grande passar
  // sem o servidor engasgar, e é o que dá o progresso real.
  async function enviarEmPedacos(job){
    const token = getRealSessionToken();
    const headers = token ? { 'Authorization': 'Bearer ' + token } : {};
    const base = 'api/admin/media/upload_chunk.php';

    const resIni = await fetch(`${base}?acao=iniciar`, {
      method: 'POST', credentials: 'include',
      headers: Object.assign({ 'Content-Type': 'application/json' }, headers),
      body: JSON.stringify({ destination_key: job.key }),
    });
    const ini = await resIni.json();
    if(!resIni.ok) throw new Error(ini.error || 'Não consegui iniciar o envio.');

    const tamParte = ini.tamanho_parte;
    const total = job.arquivo.size;
    const qtd = Math.ceil(total / tamParte);
    const partes = [];
    const inicio = Date.now();

    for(let i = 0; i < qtd; i++){
      if(job.cancelar){
        await fetch(`${base}?acao=cancelar`, {
          method: 'POST', credentials: 'include',
          headers: Object.assign({ 'Content-Type': 'application/json' }, headers),
          body: JSON.stringify({ destination_key: job.key, upload_id: ini.upload_id }),
        }).catch(() => {});
        throw new Error('cancelado');
      }

      const pedaco = job.arquivo.slice(i * tamParte, Math.min((i + 1) * tamParte, total));
      const url = `${base}?acao=parte&upload_id=${encodeURIComponent(ini.upload_id)}`
        + `&key=${encodeURIComponent(job.key)}&parte=${i + 1}`;

      const res = await fetch(url, {
        method: 'POST', credentials: 'include',
        headers: Object.assign({ 'Content-Type': 'application/octet-stream' }, headers),
        body: pedaco,
      });
      const r = await res.json();
      if(!res.ok) throw new Error(r.error || `Falhou no pedaço ${i + 1} de ${qtd}.`);
      partes.push({ parte: r.parte, etag: r.etag });

      job.enviado = Math.min((i + 1) * tamParte, total);
      job.pct = Math.round(job.enviado / total * 100);
      const decorrido = (Date.now() - inicio) / 1000;
      job.restante = decorrido / job.enviado * (total - job.enviado);
      renderPainelUploads();
    }

    const resFim = await fetch(`${base}?acao=finalizar`, {
      method: 'POST', credentials: 'include',
      headers: Object.assign({ 'Content-Type': 'application/json' }, headers),
      body: JSON.stringify({ destination_key: job.key, upload_id: ini.upload_id, partes }),
    });
    const fim = await resFim.json();
    if(!resFim.ok) throw new Error(fim.error || 'Não consegui finalizar o envio.');
    return fim.video_key;
  }

  // Um de cada vez de propósito: dois envios paralelos dividiriam a
  // mesma banda de upload e os dois ficariam lentos, sem ganho nenhum.
  async function processarFila(){
    if(uploadEmAndamento) return;
    const job = filaUploads.find(j => j.status === 'aguardando');
    if(!job) return;

    uploadEmAndamento = true;
    job.status = 'enviando';
    renderPainelUploads();

    try{
      const videoKey = await enviarEmPedacos(job);
      await apiFetch('api/admin/courses/create.php', {
        method: 'POST',
        body: JSON.stringify(Object.assign({}, job.dados, { video_source: 's3', video_key: videoKey })),
      });
      job.status = 'concluido';
      job.pct = 100;
      recarregarTelaConteudo(); // a aula nova já aparece sem recarregar a página
    }catch(e){
      job.status = e.message === 'cancelado' ? 'cancelado' : 'erro';
      job.erro = e.message;
    }finally{
      uploadEmAndamento = false;
      renderPainelUploads();
      processarFila();
    }
  }

  function enfileirarUpload(arquivo, dados, titulo){
    const nomeLimpo = arquivo.name.replace(/[^a-zA-Z0-9.-]/g, '-');
    const pasta = dados.type === 'onboarding' ? 'onboarding' : 'cursos';
    filaUploads.push({
      arquivo, dados, titulo,
      key: `${pasta}/${Date.now()}-${nomeLimpo}`,
      status: 'aguardando', pct: 0, enviado: 0, total: arquivo.size, restante: Infinity,
      cancelar: false,
    });
    renderPainelUploads();
    processarFila();
  }

  // Fechar a aba no meio do envio perde o que faltava — o navegador
  // simplesmente para de mandar os bytes. Avisa antes.
  window.addEventListener('beforeunload', (e) => {
    if(filaUploads.some(j => j.status === 'enviando' || j.status === 'aguardando')){
      e.preventDefault();
      e.returnValue = '';
    }
  });

  // ---------- Modal de "gerenciar aulas" ----------
  function openAulasModal(){
    document.getElementById('aulasModalOverlay').hidden = false;
    loadAulas();
  }
  function closeAulasModal(){
    document.getElementById('aulasModalOverlay').hidden = true;
  }

  async function loadAulas(){
    const body = document.getElementById('aulasModalBody');
    body.innerHTML = '<p>Carregando...</p>';
    try{
      const data = await apiFetch('api/admin/courses/watchers.php');
      body.innerHTML = '<ul class="aulas-list"></ul>';
      const ul = body.querySelector('.aulas-list');
      data.courses.forEach(c => {
        const arquivada = c.is_published === 0;
        const li = document.createElement('li');
        li.className = 'aulas-item' + (arquivada ? ' is-arquivada' : '');
        li.innerHTML = `
          <div class="aulas-info">
            <div class="aulas-title">${c.title}${arquivada ? ' <span class="aulas-badge">arquivada</span>' : ''}</div>
            <div class="aulas-meta">${c.area_name || 'Sem área'} · ${c.type === 'onboarding' ? 'Integração' : 'Catálogo'} · <b>${c.total_concluido}</b> concluíram</div>
          </div>
          <div class="aulas-acoes">
            <button type="button" class="aulas-btn aulas-btn-areas">Áreas</button>
            <button type="button" class="aulas-btn aulas-btn-renomear">Renomear</button>
            <button type="button" class="aulas-btn aulas-btn-arquivar">${arquivada ? 'Republicar' : 'Arquivar'}</button>
            <button type="button" class="aulas-btn aulas-btn-excluir">Excluir</button>
          </div>
        `;
        li.querySelector('.aulas-btn-areas').addEventListener('click', () => abrirAreasDaAula(c.id, c.title));
        li.querySelector('.aulas-btn-renomear').addEventListener('click', () => renomearAula(c.id, c.title));
        li.querySelector('.aulas-btn-arquivar').addEventListener('click', () => arquivarAula(c.id, arquivada));
        li.querySelector('.aulas-btn-excluir').addEventListener('click', () => excluirAula(c.id));
        ul.appendChild(li);
      });
    }catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${e.message}</p>`;
    }
  }

  // Arquivar ou excluir mexe no que o colaborador vê, mas a trilha e o
  // catálogo já estavam carregados em memória — sem recarregar, a aula
  // continuava na tela como se nada tivesse acontecido.
  async function recarregarTelaConteudo(){
    try{
      detalheCache.clear(); // senão a aula apagada volta do cache ao abrir
      await carregarConteudo();
      renderOnboarding();
      renderProgressPanel();
      if(!document.getElementById('areaModal').hidden) refreshCourseScreen();
    }catch(e){
      console.warn('[conteudo] não consegui recarregar a tela:', e.message);
    }
  }

  // Escolhe para quais áreas a aula é obrigatória. Nenhuma marcada =
  // obrigatória pra todo mundo, que é o padrão. Marcar não esconde a
  // aula de ninguém: quem é de fora continua vendo e podendo assistir,
  // só não conta como pendência dele.
  async function abrirAreasDaAula(courseId, titulo){
    const body = document.getElementById('aulasModalBody');
    const anterior = body.innerHTML;
    body.innerHTML = '<p>Carregando áreas...</p>';

    let dados;
    try {
      dados = await apiFetch('api/admin/courses/areas.php?course_id=' + encodeURIComponent(courseId));
    } catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${e.message}</p>`;
      setTimeout(loadAulas, 2500);
      return;
    }

    body.innerHTML = `
      <button type="button" class="watchers-back-btn" id="areasVoltar">&larr; Voltar</button>
      <h5 class="areas-aula-titulo">${titulo}</h5>
      <p class="admin-field-hint">Marque as áreas para as quais esta aula é <strong>obrigatória</strong>.
        Sem nenhuma marcada, vale para todos. Quem não é da área marcada continua vendo a aula —
        ela só não entra nas pendências nem na barra de progresso dessa pessoa.</p>
      <div class="areas-aula-lista">
        ${dados.areas.map(a => `
          <label class="areas-aula-item">
            <input type="checkbox" value="${a.id}" ${a.marcada ? 'checked' : ''}>
            <span>${a.name}</span>
          </label>
        `).join('')}
      </div>
      <button type="button" class="admin-modal-submit" id="areasSalvar">Salvar</button>
      <div class="admin-modal-feedback" id="areasFeedback" hidden></div>
    `;

    document.getElementById('areasVoltar').addEventListener('click', loadAulas);
    document.getElementById('areasSalvar').addEventListener('click', async () => {
      const marcadas = Array.from(body.querySelectorAll('.areas-aula-item input:checked'))
        .map(i => parseInt(i.value, 10));
      const fb = document.getElementById('areasFeedback');
      try {
        const r = await apiFetch('api/admin/courses/areas.php', {
          method: 'POST',
          body: JSON.stringify({ course_id: courseId, areas: marcadas }),
        });
        fb.hidden = false;
        fb.className = 'admin-modal-feedback ok';
        fb.textContent = r.message;
        recarregarTelaConteudo(); // a barra de progresso muda na hora
        setTimeout(loadAulas, 1200);
      } catch(e){
        fb.hidden = false;
        fb.className = 'admin-modal-feedback erro';
        fb.textContent = e.message;
      }
    });
  }

  // Só troca o texto: não mexe em vídeo, área nem quiz, e não recria a
  // linha — então o histórico de quem já assistiu aquela aula continua
  // valendo, e quem estiver no meio da trilha não perde o progresso.
  async function renomearAula(courseId, tituloAtual){
    const novo = prompt('Novo nome da aula:', tituloAtual);
    if(novo === null) return;
    if(!novo.trim()){
      alert('O nome não pode ficar vazio.');
      return;
    }
    if(novo.trim() === tituloAtual) return;
    try{
      await apiFetch('api/admin/courses/update.php', {
        method: 'POST',
        body: JSON.stringify({ course_id: courseId, title: novo.trim() }),
      });
      loadAulas();
      recarregarTelaConteudo(); // o nome muda na trilha sem recarregar a página
    }catch(e){
      alert('Não deu certo: ' + e.message);
    }
  }

  async function arquivarAula(courseId, estaArquivada){
    try{
      const data = await apiFetch('api/admin/courses/archive.php', {
        method: 'POST',
        body: JSON.stringify({ course_id: courseId, published: estaArquivada ? 1 : 0 }),
      });
      alert(data.message);
      loadAulas();
      recarregarTelaConteudo();
    }catch(e){
      alert('Não deu certo: ' + e.message);
    }
  }

  // Duas etapas de propósito: a primeira chamada volta 409 com o tamanho
  // do estrago (quantas pessoas perdem o histórico), e só depois pedimos
  // o título digitado. Não usa apiFetch porque ele descarta o corpo da
  // resposta de erro, que é justamente onde vem o impacto.
  async function excluirAula(courseId){
    try{
      const token = getRealSessionToken();
      const headers = { 'Content-Type': 'application/json' };
      if(token) headers['Authorization'] = 'Bearer ' + token;

      const res = await fetch('api/admin/courses/delete.php', {
        method: 'POST',
        credentials: 'include',
        headers,
        body: JSON.stringify({ course_id: courseId }),
      });
      const info = await res.json();

      if(res.status !== 409){
        alert(info.error || 'Não consegui checar essa aula.');
        return;
      }

      const imp = info.impacto;
      const aviso = `EXCLUSÃO DEFINITIVA\n\n"${imp.titulo}"\n\n`
        + `Isso vai apagar para sempre:\n`
        + `· ${imp.colaboradores_com_progresso} registro(s) de quem assistiu\n`
        + `· ${imp.respostas_de_quiz} resposta(s) de quiz\n\n`
        + `Esse histórico não volta. Se a ideia é só tirar do ar, cancele e use "Arquivar".\n\n`
        + `Para confirmar, digite o título exato da aula:`;

      const digitado = prompt(aviso, '');
      if(digitado === null) return;

      const resDel = await fetch('api/admin/courses/delete.php', {
        method: 'POST',
        credentials: 'include',
        headers,
        body: JSON.stringify({ course_id: courseId, confirmacao: digitado }),
      });
      const resultado = await resDel.json();

      if(!resDel.ok){
        alert(resultado.error || 'Não foi possível excluir.');
        return;
      }
      alert(`${resultado.message}\n\n${resultado.removido.registros_de_progresso_apagados} registro(s) de progresso apagados.`
        + (resultado.video_mantido_no_s3 ? `\n\nO arquivo do vídeo continua no S3: ${resultado.video_mantido_no_s3}` : ''));
      loadAulas();
      recarregarTelaConteudo();
    }catch(e){
      alert('Não deu certo: ' + e.message);
    }
  }

  // ---------- Modal de "quem assistiu" ----------
  function openWatchersModal(){
    document.getElementById('watchersModalOverlay').hidden = false;
    loadWatchersOverview();
  }
  function closeWatchersModal(){
    document.getElementById('watchersModalOverlay').hidden = true;
  }

  async function loadWatchersOverview(){
    const body = document.getElementById('watchersModalBody');
    document.getElementById('watchersModalSubtitle').textContent = 'Clique num vídeo pra ver os nomes de quem já assistiu.';
    body.innerHTML = '<p>Carregando...</p>';
    try{
      const data = await apiFetch('api/admin/courses/watchers.php');
      body.innerHTML = '<ul class="watchers-course-list"></ul>';
      const ul = body.querySelector('.watchers-course-list');
      data.courses.forEach(c => {
        const li = document.createElement('li');
        li.className = 'watchers-course-item';
        li.innerHTML = `
          <div>
            <div class="wc-title">${c.title}</div>
            <div class="wc-area">${c.area_name} · ${c.type === 'onboarding' ? 'Integração' : 'Catálogo'}</div>
          </div>
          <div class="wc-counts"><b>${c.total_concluido}</b> concluíram · ${c.total_em_andamento} em andamento</div>
        `;
        li.addEventListener('click', () => loadWatchersDetail(c.id, c.title));
        ul.appendChild(li);
      });
    }catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${e.message}</p>`;
    }
  }

  async function loadWatchersDetail(courseId, courseTitle, nomeFiltro, areaFiltro){
    nomeFiltro = nomeFiltro || '';
    areaFiltro = areaFiltro || '';
    const body = document.getElementById('watchersModalBody');
    document.getElementById('watchersModalSubtitle').textContent = courseTitle;
    body.innerHTML = '<p>Carregando...</p>';
    try{
      const query = new URLSearchParams({ course_id: courseId, nome: nomeFiltro, area: areaFiltro });
      const data = await apiFetch('api/admin/courses/watchers.php?' + query.toString());

      const opcoesArea = (data.areas || []).map(a =>
        `<option value="${a.slug}" ${a.slug === areaFiltro ? 'selected' : ''}>${a.name}</option>`
      ).join('');

      const rows = data.watchers.map(w => `
        <tr>
          <td>${w.name}</td>
          <td>${w.area_name || '—'}</td>
          <td>${w.cargo || '—'}</td>
          <td><span class="watchers-status ${w.status}">${w.status === 'concluido' ? 'Concluído' : 'Em andamento'}</span></td>
          <td>${w.completed_at ? new Date(w.completed_at).toLocaleDateString('pt-BR') : '—'}</td>
        </tr>
      `).join('');

      body.innerHTML = `
        <button type="button" class="watchers-back-btn" id="watchersBackBtn">← Voltar pra lista de vídeos</button>
        <div class="admin-filtros">
          <input type="text" id="watchersFiltroNome" placeholder="Buscar por nome..." value="${nomeFiltro.replace(/"/g, '&quot;')}">
          <select id="watchersFiltroArea">
            <option value="">Todos os departamentos</option>
            ${opcoesArea}
          </select>
        </div>
        ${data.watchers.length === 0 ? '<p>Ninguém encontrado com esse filtro.</p>' : `
        <table class="watchers-table">
          <thead><tr><th>Nome</th><th>Departamento</th><th>Cargo</th><th>Status</th><th>Concluído em</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>`}
      `;
      document.getElementById('watchersBackBtn').addEventListener('click', loadWatchersOverview);

      let debounceWatchers = null;
      document.getElementById('watchersFiltroNome').addEventListener('input', (e) => {
        clearTimeout(debounceWatchers);
        const valor = e.target.value;
        debounceWatchers = setTimeout(() => loadWatchersDetail(courseId, courseTitle, valor, areaFiltro), 400);
      });
      document.getElementById('watchersFiltroArea').addEventListener('change', (e) => {
        loadWatchersDetail(courseId, courseTitle, nomeFiltro, e.target.value);
      });
    }catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${e.message}</p>`;
    }
  }

  // ---------- Modal de "Acessos" ----------
  function openAcessosModal(){
    document.getElementById('acessosModalOverlay').hidden = false;
    document.getElementById('acessosFiltroNome').value = '';
    loadAcessos();
  }
  function closeAcessosModal(){
    document.getElementById('acessosModalOverlay').hidden = true;
  }

  async function loadAcessos(){
    const body = document.getElementById('acessosModalBody');
    const nomeFiltro = document.getElementById('acessosFiltroNome').value.trim();
    const areaSelect = document.getElementById('acessosFiltroArea');
    const areaFiltro = areaSelect.value;

    body.innerHTML = '<p>Carregando...</p>';
    try{
      const query = new URLSearchParams({ nome: nomeFiltro, area: areaFiltro });
      const data = await apiFetch('api/admin/users_progress.php?' + query.toString());

      // Preenche o <select> de área só na primeira vez (senão perde a
      // seleção atual toda vez que recarrega a lista).
      if(areaSelect.options.length <= 1){
        (data.areas || []).forEach(a => {
          const opt = document.createElement('option');
          opt.value = a.slug;
          opt.textContent = a.name;
          areaSelect.appendChild(opt);
        });
      }

      document.getElementById('acessosModalSubtitle').textContent =
        `${data.total_cursos_disponiveis} cursos disponíveis no catálogo · ${data.total_onboarding_modulos} módulos de integração`;

      if(data.users.length === 0){
        body.innerHTML = '<p>Ninguém encontrado com esse filtro.</p>';
        return;
      }

      const rows = data.users.map(u => `
        <tr>
          <td>${u.name}</td>
          <td>${u.area_name || '—'}</td>
          <td>${u.cargo || '—'}</td>
          <td>${u.distinct_access_count}</td>
          <td>${u.modules_done} de ${u.total_onboarding_modules}${u.completed ? ' ✓' : ''}</td>
          <td>${u.last_access_date ? new Date(u.last_access_date).toLocaleDateString('pt-BR') : '—'}</td>
        </tr>
      `).join('');

      body.innerHTML = `
        <table class="watchers-table">
          <thead><tr><th>Nome</th><th>Departamento</th><th>Cargo</th><th>Acessos</th><th>Integração</th><th>Último acesso</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
      `;
    }catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${e.message}</p>`;
    }
  }

  document.addEventListener('DOMContentLoaded', async () => {
    const diagnostico = { url: window.location.href, tentativas: [] };

    await tryRealSsoLogin(diagnostico);
    updateGreetingBanner(); // atualiza a saudação com o nome real, se o login deu certo

    // Só aqui a sessão existe, e os endpoints de conteúdo exigem login —
    // por isso a trilha e o catálogo carregam neste ponto, e não no
    // início do arquivo como eram quando estavam fixos no código.
    try {
      await carregarConteudo();
      renderOnboarding();
      renderProgressPanel();
    } catch(e){
      console.error('[conteudo] falha ao carregar:', e.message);
      const trilha = document.getElementById('onboardingPath') || document.querySelector('.onb-track');
      if(trilha) trilha.insertAdjacentHTML('beforebegin',
        `<p style="text-align:center;color:#C4212C;font-size:14px">Não consegui carregar as aulas: ${e.message}</p>`);
    }

    const isAdmin = await checkIsRealAdmin();
    diagnostico.isAdmin = isAdmin;

    // A trilha é desenhada fora deste bloco e não enxerga nem o estado
    // de admin nem renomearAula(). Publicar os dois aqui é o que permite
    // editar o nome clicando direto no balão, sem abrir o painel.
    window.mseEhAdmin = isAdmin;
    window.mseRenomearAula = renomearAula;
    if(isAdmin && typeof renderOnboarding === 'function') renderOnboarding();
    diagnostico.temTokenSalvo = !!getRealSessionToken();

    // mostrarPainelDiagnostico(diagnostico); // desativado — só reativar se precisar depurar de novo

    if(isAdmin){
      const toolbar = document.getElementById('adminToolbar');
      if(toolbar) toolbar.hidden = false;
      ['btnAdicionarPessoas', 'btnAdicionarVideo', 'btnQuemAssistiu', 'btnAcessos', 'btnGerenciarAulas'].forEach(id => {
        const btn = document.getElementById(id);
        if(btn) btn.hidden = false;
      });
      // Lembra se a pessoa tinha minimizado da última vez que usou —
      // não fica reabrindo sozinho toda hora sem necessidade.
      if(localStorage.getItem('mse_academy_admin_toolbar_minimizada') === '1'){
        aplicarEstadoAdminToolbar(true);
      }
    }

    function aplicarEstadoAdminToolbar(minimizado){
      const toolbar = document.getElementById('adminToolbar');
      const btnMin = document.getElementById('btnMinimizarAdmin');
      if(!toolbar) return;
      toolbar.classList.toggle('is-minimizado', minimizado);
      if(btnMin) btnMin.setAttribute('title', minimizado ? 'Expandir' : 'Minimizar');
      localStorage.setItem('mse_academy_admin_toolbar_minimizada', minimizado ? '1' : '0');
    }

    // Mesma seta faz os dois sentidos — minimiza se estiver expandido,
    // reabre se estiver minimizado.
    const btnMinimizar = document.getElementById('btnMinimizarAdmin');
    if(btnMinimizar) btnMinimizar.addEventListener('click', () => {
      const toolbar = document.getElementById('adminToolbar');
      aplicarEstadoAdminToolbar(!toolbar.classList.contains('is-minimizado'));
    });

    const btnAdd = document.getElementById('btnAdicionarPessoas');
    if(btnAdd) btnAdd.addEventListener('click', openAdminModal);

    const btnClose = document.getElementById('adminModalClose');
    if(btnClose) btnClose.addEventListener('click', closeAdminModal);

    const overlay = document.getElementById('adminModalOverlay');
    if(overlay) overlay.addEventListener('click', (e) => {
      if(e.target === overlay) closeAdminModal();
    });

    const btnVideo = document.getElementById('btnAdicionarVideo');
    if(btnVideo) btnVideo.addEventListener('click', openVideoModal);
    const btnVideoClose = document.getElementById('adminVideoModalClose');
    if(btnVideoClose) btnVideoClose.addEventListener('click', closeVideoModal);
    const btnVideoSubmit = document.getElementById('videoModalSubmit');
    if(btnVideoSubmit) btnVideoSubmit.addEventListener('click', submitAddVideo);
    const videoTypeSelect = document.getElementById('videoModalType');
    if(videoTypeSelect) videoTypeSelect.addEventListener('change', atualizarVisibilidadeArea);

    const videoOrigemSelect = document.getElementById('videoModalOrigem');
    if(videoOrigemSelect) videoOrigemSelect.addEventListener('change', atualizarVisibilidadeOrigemVideo);
    const videoOverlay = document.getElementById('videoModalOverlay');
    if(videoOverlay) videoOverlay.addEventListener('click', (e) => {
      if(e.target === videoOverlay) closeVideoModal();
    });

    const btnWatchers = document.getElementById('btnQuemAssistiu');
    if(btnWatchers) btnWatchers.addEventListener('click', openWatchersModal);
    const btnWatchersClose = document.getElementById('watchersModalClose');
    if(btnWatchersClose) btnWatchersClose.addEventListener('click', closeWatchersModal);
    const watchersOverlay = document.getElementById('watchersModalOverlay');
    if(watchersOverlay) watchersOverlay.addEventListener('click', (e) => {
      if(e.target === watchersOverlay) closeWatchersModal();
    });

    const btnUploadsFechar = document.getElementById('uploadsPainelFechar');
    if(btnUploadsFechar) btnUploadsFechar.addEventListener('click', () => {
      document.getElementById('uploadsPainel').classList.toggle('is-oculto');
    });

    const btnAulas = document.getElementById('btnGerenciarAulas');
    if(btnAulas) btnAulas.addEventListener('click', openAulasModal);
    const btnAulasClose = document.getElementById('aulasModalClose');
    if(btnAulasClose) btnAulasClose.addEventListener('click', closeAulasModal);
    const aulasOverlay = document.getElementById('aulasModalOverlay');
    if(aulasOverlay) aulasOverlay.addEventListener('click', (e) => {
      if(e.target === aulasOverlay) closeAulasModal();
    });

    const btnAcessos = document.getElementById('btnAcessos');
    if(btnAcessos) btnAcessos.addEventListener('click', openAcessosModal);
    const btnAcessosClose = document.getElementById('acessosModalClose');
    if(btnAcessosClose) btnAcessosClose.addEventListener('click', closeAcessosModal);
    const acessosOverlay = document.getElementById('acessosModalOverlay');
    if(acessosOverlay) acessosOverlay.addEventListener('click', (e) => {
      if(e.target === acessosOverlay) closeAcessosModal();
    });
    const filtroNome = document.getElementById('acessosFiltroNome');
    const filtroArea = document.getElementById('acessosFiltroArea');
    // Debounce simples no campo de texto — não busca a cada tecla, só
    // depois de meio segundo sem digitar, pra não sobrecarregar a API.
    let debounceAcessos = null;
    if(filtroNome) filtroNome.addEventListener('input', () => {
      clearTimeout(debounceAcessos);
      debounceAcessos = setTimeout(loadAcessos, 400);
    });
    if(filtroArea) filtroArea.addEventListener('change', loadAcessos);

    const btnSubmit = document.getElementById('adminModalSubmit');
    if(btnSubmit) btnSubmit.addEventListener('click', submitAddPerson);
  });
})();
