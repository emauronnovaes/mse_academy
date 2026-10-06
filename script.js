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
        statAulasAssistidas: data.aulas_assistidas,
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
  /**
   * A trilha inteira foi concluída?
   *
   * O "ONBOARDING.length > 0" não é detalhe. Com a trilha vazia — nenhuma
   * aula publicada ainda, ou a API fora do ar — o índice atual é 0 e o
   * "0 >= 0" dava concluído: a pessoa recebia "Parabéns, você concluiu a
   * integração" sem ter assistido nada, e o baú aparecia aberto na trilha.
   */
  function trilhaConcluida(indiceAtual){
    return ONBOARDING.length > 0 && indiceAtual >= ONBOARDING.length;
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
  let onbPresenca = null; // aviso "Você ainda está aí?" do módulo aberto
  let onbAtividades = null; // atividades durante o vídeo do módulo aberto

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
    const allDone = trilhaConcluida(currentIdx);
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
    const allDone = trilhaConcluida(currentIdx);

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
            <p>${trilhaTemPerguntas() ? `Você acertou ${quiz.correct} de ${quiz.total} perguntas (${quiz.pct}%). ` : ''}Agora vá até a aba <strong>Cursos</strong> e assista às aulas que você precisa.</p>
          </div>
        </div>` : `
        <div class="onb-module onb-congrats">
          <div class="onb-congrats-inner">
            <div class="onb-congrats-emoji onb-congrats-emoji-warn"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></div>
            <h4>Quase lá — falta acertar mais uma pergunta</h4>
            <p>Você assistiu todos os vídeos, mas acertou só ${quiz.correct} de ${quiz.total} perguntas (${quiz.pct}%). Pra abrir o baú, precisa de pelo menos 75% (3 de 4). Clique em um módulo acima, assista de novo e responda com atenção.</p>
          </div>
        </div>`;

      // A comemoração só faz sentido com o baú aberto. soltarFogos() já se
      // segura sozinha pra não repetir na mesma visita, então chamar aqui
      // toda vez que o painel é desenhado não vira fogo em série.
      if(passed) soltarFogos();
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
    if(onbPresenca){ onbPresenca.parar(); onbPresenca = null; }
    if(onbAtividades){ onbAtividades.parar(); onbAtividades = null; }
    encerrarSessaoPresenca(); // trocou de módulo: check-out do anterior

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
          <div class="vid-player-wrap tem-controles">
            <div id="onb-yt-${mod.id}"></div>
            <button type="button" class="vid-fullscreen-btn" aria-label="Tela cheia">
              <i class="fa-solid fa-display" aria-hidden="true"></i>
            </button>
          </div>
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

      iniciarSessaoPresenca(mod.id);
      loadYouTubeApi().then(() => {
        const player = new YT.Player(`onb-yt-${mod.id}`, {
          videoId: detalhe.youtube_id,
          playerVars: isRewatch
            // fs:0 também na revisão: na tela cheia do YouTube nada da
            // Academy aparece por cima, nem o "Você ainda está aí?". A tela
            // cheia fica com o botão da Academy, que expande a moldura.
            ? { rel: 0, modestbranding: 1, fs: 0 }
            // fs:0 tira o botão de tela cheia DO YOUTUBE. Com ele, quem
            // ia pra tela cheia era o player, e aí o YouTube mostrava a
            // barra dele — dava pra ver e arrastar o vídeo. O botão de
            // tela cheia da Academy continua funcionando: ele expande a
            // moldura inteira, não o player, então controls:0 continua
            // valendo e a barra não aparece.
            : { controls: 0, disablekb: 1, rel: 0, modestbranding: 1, fs: 0 },
          events: {
            onStateChange: (e) => {
              if(e.data === YT.PlayerState.ENDED){ onbSetWatchPct(mod, 1); encerrarSessaoPresenca(); }
            }
          }
        });
        onbPlayer = player;
        ligarBotaoTelaCheia(body);
        onbPresenca = presencaNoYouTube(body.querySelector('.vid-player-wrap'), player, mod.id);
        if(isRewatch) return;
        onbAtividades = ligarAtividadesNoVideo(body.querySelector('.vid-player-wrap'), mod.questions, controlesDoYouTube(player));

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
        <div class="vid-player-wrap tem-controles">
          <video id="onb-video-${mod.id}" src="${videoSrc}" controls controlslist="nofullscreen" playsinline></video>
          <button type="button" class="vid-fullscreen-btn" aria-label="Tela cheia">
            <i class="fa-solid fa-display" aria-hidden="true"></i>
          </button>
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
    iniciarSessaoPresenca(mod.id);
    ligarBotaoTelaCheia(body);
    onbPresenca = presencaNoVideo(body.querySelector('.vid-player-wrap'), videoEl, mod.id);

    if(isRewatch){
      return; // controles nativos cuidam de tudo — não precisa rastrear progresso
    }
    onbAtividades = ligarAtividadesNoVideo(body.querySelector('.vid-player-wrap'), mod.questions, controlesDoVideo(videoEl));

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
    videoEl.addEventListener('ended', () => { onbSetWatchPct(mod, 1); encerrarSessaoPresenca(); });
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

  // ================================================================
  // ---------- "Você ainda está aí?" ----------
  // ================================================================
  // De tempos em tempos o vídeo pausa sozinho e pergunta se a pessoa
  // continua assistindo. Só volta a tocar com o clique em "Estou aqui":
  // quem deixou a aula rodando e saiu não acumula progresso, porque o
  // vídeo fica parado até alguém voltar.
  //
  // O primeiro aviso cai num ponto sorteado entre 30% e 70% do vídeo — pra
  // aula curta também ter um — e nunca depois de 8 minutos. Nas aulas
  // longas, repete a cada 6 a 10 minutos de vídeo tocando. Sorteado pra
  // não dar pra prever.
  const PRESENCA_PRIMEIRO_MAX_SEG = 8 * 60;
  const PRESENCA_INTERVALO_MIN_SEG = 6 * 60;
  const PRESENCA_INTERVALO_MAX_SEG = 10 * 60;
  const PRESENCA_VIDEO_MIN_SEG = 60; // vídeo mais curto que isso não pergunta

  // Nome próprio de propósito: já existe um sortear(lista, quantos) mais
  // abaixo, e a declaração de baixo substituía esta em silêncio.
  function sortearEntre(min, max){ return min + Math.random() * (max - min); }

  /**
   * Liga o aviso num player. O aviso é desenhado dentro da moldura
   * (.vid-player-wrap) pra continuar visível na tela cheia da Academy.
   * `tocando`, `pausar`, `tocar` e `duracao` escondem a diferença entre
   * o <video> e o player do YouTube.
   */
  function criarChecagemDePresenca(wrap, { tocando, pausar, tocar, duracao, aoConfirmar }){
    let assistido = 0;  // segundos de vídeo tocando desde o último aviso
    let proximo = null; // quantos segundos tocando até o próximo aviso
    let aviso = null;
    let ultimo = Date.now();

    const timer = setInterval(() => {
      if(!wrap.isConnected){ parar(); return; } // player saiu da tela
      const agora = Date.now();
      const passou = (agora - ultimo) / 1000;
      ultimo = agora;

      if(aviso){
        // Esperando o clique: se o vídeo voltar a tocar por outro caminho
        // (barra de espaço, controles nativos), segura de novo.
        if(tocando()) pausar();
        return;
      }
      if(!tocando()) return;

      if(proximo === null){
        const total = duracao();
        if(!(total > 0)) return; // duração ainda não carregou
        if(total < PRESENCA_VIDEO_MIN_SEG){ parar(); return; }
        proximo = Math.min(sortearEntre(0.3, 0.7) * total, PRESENCA_PRIMEIRO_MAX_SEG);
      }

      // Limite de 2s por volta: com a aba em segundo plano o timer atrasa,
      // e o "buraco" não pode virar tempo assistido de uma vez.
      assistido += Math.min(passou, 2);
      if(assistido >= proximo) perguntar();
    }, 1000);

    function perguntar(){
      pausar();
      // Último recurso: todo player já usa a tela cheia da moldura, que
      // mostra o aviso por cima. Se mesmo assim o vídeo estiver na tela
      // cheia dele, ela esconderia o aviso — então sai dela.
      if(document.fullscreenElement && document.fullscreenElement !== wrap){
        document.exitFullscreen().catch(() => {});
      }

      aviso = document.createElement('div');
      aviso.className = 'vid-presenca';
      aviso.setAttribute('role', 'alertdialog');
      aviso.setAttribute('aria-label', 'Você ainda está aí?');
      aviso.innerHTML = `
        <div class="vid-presenca-card">
          <p class="vid-presenca-titulo">Você ainda está aí?</p>
          <p class="vid-presenca-texto">Pausamos o vídeo. Clique abaixo para continuar assistindo.</p>
          <button type="button" class="vid-presenca-btn">
            <i class="fa-solid fa-play" aria-hidden="true"></i> Estou aqui
          </button>
        </div>
      `;
      wrap.appendChild(aviso);

      const btn = aviso.querySelector('.vid-presenca-btn');
      btn.addEventListener('click', () => {
        aviso.remove();
        aviso = null;
        assistido = 0;
        proximo = sortearEntre(PRESENCA_INTERVALO_MIN_SEG, PRESENCA_INTERVALO_MAX_SEG);
        ultimo = Date.now();
        tocar();
        if(aoConfirmar) aoConfirmar();
      });
      btn.focus();
    }

    function parar(){
      clearInterval(timer);
      if(aviso){ aviso.remove(); aviso = null; }
    }

    return { parar };
  }

  // O mesmo jeito de mandar no <video> e no player do YouTube, pro aviso de
  // presença e pras atividades não precisarem saber qual dos dois é.
  function controlesDoVideo(videoEl){
    return {
      tocando: () => !videoEl.paused && !videoEl.ended,
      pausar: () => videoEl.pause(),
      tocar: () => { videoEl.play().catch(() => {}); },
      duracao: () => videoEl.duration,
      posicao: () => videoEl.currentTime,
    };
  }

  function controlesDoYouTube(player){
    // Os métodos do player do YouTube só existem depois do onReady.
    const pronto = () => typeof player.getPlayerState === 'function';
    return {
      tocando: () => pronto() && player.getPlayerState() === YT.PlayerState.PLAYING,
      pausar: () => { if(pronto()) player.pauseVideo(); },
      tocar: () => { if(pronto()) player.playVideo(); },
      duracao: () => (pronto() ? player.getDuration() : 0),
      posicao: () => (pronto() ? player.getCurrentTime() : NaN),
    };
  }

  // Cada "Estou aqui" é registrado no servidor: é a evidência de presença
  // que aparece na lista de presença do treinamento. Falhar aqui não
  // atrapalha quem está assistindo.
  function registrarPresenca(courseId){
    apiPost('api/progress/presenca.php', { course_id: courseId })
      .catch(e => console.warn('[presença] não consegui registrar:', e.message));
  }

  // ---------- Log de presença: check-in ao abrir, check-out ao sair ----------
  // Cada vez que a pessoa abre o vídeo é um check-in; quando sai — fecha o
  // vídeo, troca de aula, fecha a aba ou o vídeo termina — é um check-out.
  // Data, hora e % assistido quem grava é o servidor (progress/evento.php).
  let sessaoPresenca = null; // { courseId } do vídeo aberto agora

  function enviarEventoPresenca(courseId, evento, saindoDaPagina){
    const token = tokenSessao();
    fetch('api/progress/evento.php', {
      method: 'POST',
      credentials: 'include',
      // keepalive: a requisição sobrevive à aba sendo fechada — sem isso o
      // check-out de quem fecha a aba nunca chegava.
      keepalive: !!saindoDaPagina,
      headers: Object.assign({ 'Content-Type': 'application/json' }, token ? { 'Authorization': 'Bearer ' + token } : {}),
      body: JSON.stringify({ course_id: courseId, evento }),
    }).catch(e => console.warn('[presença] não consegui registrar ' + evento + ':', e.message));
  }

  function iniciarSessaoPresenca(courseId){
    encerrarSessaoPresenca();
    sessaoPresenca = { courseId };
    enviarEventoPresenca(courseId, 'checkin');
  }

  function encerrarSessaoPresenca(saindoDaPagina){
    if(!sessaoPresenca) return;
    const { courseId } = sessaoPresenca;
    sessaoPresenca = null;
    enviarEventoPresenca(courseId, 'checkout', saindoDaPagina);
  }

  window.addEventListener('pagehide', () => encerrarSessaoPresenca(true));

  function presencaNoVideo(wrap, videoEl, courseId){
    return criarChecagemDePresenca(wrap, Object.assign(controlesDoVideo(videoEl), {
      aoConfirmar: () => registrarPresenca(courseId),
    }));
  }

  function presencaNoYouTube(wrap, player, courseId){
    return criarChecagemDePresenca(wrap, Object.assign(controlesDoYouTube(player), {
      aoConfirmar: () => registrarPresenca(courseId),
    }));
  }

  // ================================================================
  // ---------- Atividades durante o vídeo ----------
  // ================================================================
  // Pergunta com momento_seg aparece no meio do vídeo: no segundo marcado o
  // vídeo pausa e a pergunta cobre o player. Só volta a tocar depois da
  // resposta certa — errou, tenta de novo, igual às perguntas do fim. Quem
  // confere é o servidor (quiz/submit.php), que também exige que a pessoa
  // tenha chegado àquele ponto do vídeo. A trava de avanço garante que não
  // dá pra pular por cima: o vídeo passa pelo segundo marcado.
  function ligarAtividadesNoVideo(wrap, perguntas, { posicao, tocando, pausar, tocar }){
    const atividades = (perguntas || [])
      .filter(q => q.momento_seg != null)
      .sort((a, b) => a.momento_seg - b.momento_seg);
    const resolvidas = new Set();
    let aberta = null;
    const controle = { pendentes: () => atividades.length - resolvidas.size, parar };
    if(!atividades.length) return controle;

    const timer = setInterval(() => {
      if(!wrap.isConnected){ parar(); return; } // player saiu da tela
      if(aberta){
        // Esperando a resposta: se o vídeo voltar a tocar por outro caminho
        // (barra de espaço, controles nativos), segura de novo.
        if(tocando()) pausar();
        return;
      }
      const pos = posicao();
      if(!isFinite(pos)) return;
      const proxima = atividades.find(q => !resolvidas.has(q.id) && pos >= q.momento_seg);
      if(proxima) mostrar(proxima);
    }, 250);

    function mostrar(q){
      pausar();
      // Mesmo cuidado do aviso de presença: na tela cheia do próprio vídeo
      // a atividade ficaria escondida.
      if(document.fullscreenElement && document.fullscreenElement !== wrap){
        document.exitFullscreen().catch(() => {});
      }
      const mmss = String(Math.floor(q.momento_seg / 60)).padStart(2, '0') + ':' + String(q.momento_seg % 60).padStart(2, '0');
      aberta = document.createElement('div');
      aberta.className = 'vid-atividade';
      aberta.setAttribute('role', 'dialog');
      aberta.setAttribute('aria-label', 'Atividade do vídeo');
      aberta.innerHTML = `
        <div class="vid-atividade-card">
          <span class="vid-atividade-tag">Atividade · ${mmss}</span>
          <p class="vid-atividade-pergunta">${escaparHtml(q.question_text)}</p>
          <div class="vid-atividade-opcoes">
            ${q.options.map(o => `
              <button type="button" class="vid-atividade-opcao" data-option-id="${o.id}">${escaparHtml(o.option_text)}</button>
            `).join('')}
          </div>
          <div class="vid-atividade-feedback" hidden></div>
        </div>
      `;
      wrap.appendChild(aberta);

      const caixa = aberta;
      const botoes = caixa.querySelectorAll('.vid-atividade-opcao');
      const fb = caixa.querySelector('.vid-atividade-feedback');
      botoes.forEach(btn => btn.addEventListener('click', async () => {
        botoes.forEach(b => { b.disabled = true; });
        let certo;
        try{
          const r = await apiPost('api/quiz/submit.php', {
            question_id: q.id,
            option_id: parseInt(btn.dataset.optionId, 10),
          });
          certo = !!r.correct;
        }catch(e){
          fb.hidden = false;
          fb.className = 'vid-atividade-feedback bad';
          fb.textContent = e.message;
          botoes.forEach(b => { b.disabled = false; });
          return;
        }
        fb.hidden = false;
        if(certo){
          btn.classList.add('correct');
          fb.className = 'vid-atividade-feedback ok';
          fb.textContent = 'Isso aí! Voltando ao vídeo...';
          resolvidas.add(q.id);
          setTimeout(() => {
            if(aberta === caixa){ caixa.remove(); aberta = null; }
            tocar();
          }, 1200);
        } else {
          btn.classList.add('incorrect');
          fb.className = 'vid-atividade-feedback bad';
          fb.textContent = 'Quase lá — tente de novo.';
          setTimeout(() => {
            botoes.forEach(b => { b.disabled = false; b.classList.remove('incorrect'); });
            fb.hidden = true;
          }, 1200);
        }
      }));
      if(botoes[0]) botoes[0].focus();
    }

    function parar(){
      clearInterval(timer);
      if(aberta){ aberta.remove(); aberta = null; }
    }

    return controle;
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

  // ================================================================
  // ---------- Fogos de artifício ----------
  // ================================================================
  // Comemoração de quem terminou a trilha de integração. Em canvas e sem
  // biblioteca nenhuma: o site é todo auto-hospedado, sem CDN, e puxar um
  // pacote de fora só pra isso trocaria alguns segundos de festa por mais
  // um arquivo pra carregar em toda visita.
  //
  // Só na vez em que a pessoa conclui. Depois disso, nunca mais: ela vai
  // reabrir o baú muitas vezes pra rever a trilha, e fogos em toda visita
  // deixam de ser comemoração e viram uma animação no caminho.
  //
  // A marca fica junto com o progresso da trilha, que já é guardado no
  // navegador — então sobrevive a recarregar e a fechar a aba. Vale por
  // navegador, igual ao resto do progresso local: quem concluiu no
  // computador e abrir no celular vê uma vez lá também.
  //
  // A marca de sessão fica na PRÓPRIA função, e não numa variável solta
  // com "let", porque a declaração da função é içada pro topo mas a da
  // variável não: do jeito anterior, se qualquer linha antes daqui
  // falhasse, chamar soltarFogos() estourava com "Cannot access before
  // initialization" em vez de apenas não comemorar.
  function jaComemorou(){
    try{ return !!loadOnboardingProgress().fogosVistos; }
    catch(e){ return false; } // sem storage, comemora — melhor que nunca comemorar
  }

  function marcarQueComemorou(){
    try{
      // Marca no objeto que já está em memória, em vez de trocá-lo por um
      // recém-lido. Qualquer save posterior do progresso (concluir aula,
      // responder pergunta) usa esse mesmo objeto — se a marca estivesse
      // só numa cópia, o próximo save a apagaria e os fogos voltariam.
      onbProgress.fogosVistos = true;
      saveOnboardingProgress(onbProgress);
    }catch(e){
      // Se onbProgress ainda não existe, grava direto no armazenamento.
      try{
        const prog = loadOnboardingProgress();
        prog.fogosVistos = true;
        saveOnboardingProgress(prog);
      }catch(e2){ /* navegador sem storage: só não lembra na próxima */ }
    }
  }

  function soltarFogos(){
    if(soltarFogos.jaRodou) return;
    // Quem pediu menos movimento no sistema não recebe a animação. É a
    // mesma preferência que já pausa o vídeo do banner. Sai sem marcar:
    // se um dia desligar a preferência, ainda ganha a comemoração.
    if(window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if(jaComemorou()) return;

    soltarFogos.jaRodou = true;
    // Marca antes de animar: se algo estourar no meio, o pior que acontece
    // é a pessoa perder os fogos, não recebê-los de novo toda vez.
    marcarQueComemorou();

    const DURACAO = 5000;
    const CORES = ['#C4212C', '#F2B705', '#FFFFFF', '#2EA05A', '#4A7BD4', '#E8843C'];

    const tela = document.createElement('canvas');
    tela.className = 'fogos-canvas';
    tela.setAttribute('aria-hidden', 'true'); // decoração: leitor de tela ignora
    document.body.appendChild(tela);
    const ctx = tela.getContext('2d');

    let larg = 0, alt = 0;
    function medir(){
      // devicePixelRatio senão fica borrado em tela de notebook boa.
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      larg = window.innerWidth; alt = window.innerHeight;
      tela.width = larg * dpr; tela.height = alt * dpr;
      tela.style.width = larg + 'px'; tela.style.height = alt + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    medir();
    window.addEventListener('resize', medir);

    const foguetes = [];
    const fagulhas = [];

    function lancar(){
      foguetes.push({
        x: larg * (0.15 + Math.random() * 0.7),
        y: alt,
        vy: -(alt / 75) * (0.85 + Math.random() * 0.3),
        alvo: alt * (0.15 + Math.random() * 0.3),
        cor: CORES[Math.floor(Math.random() * CORES.length)],
      });
    }

    function estourar(f){
      const quantas = 64 + Math.floor(Math.random() * 34);
      for(let i = 0; i < quantas; i++){
        const ang = (Math.PI * 2 * i) / quantas + Math.random() * 0.25;
        const forca = 2.1 + Math.random() * 4.4;
        fagulhas.push({
          x: f.x, y: f.y,
          vx: Math.cos(ang) * forca,
          vy: Math.sin(ang) * forca,
          vida: 1,
          decai: 0.012 + Math.random() * 0.014,
          cor: f.cor,
        });
      }
    }

    const inicio = performance.now();
    let proximoLancamento = 0;

    function quadro(agora){
      // Se a pessoa saiu da página, o canvas some do DOM e o laço para —
      // sem isso ele continuaria rodando à toa em segundo plano.
      if(!tela.isConnected) return;

      const passado = agora - inicio;

      // Rastro: em vez de apagar tudo, tira um pouco da opacidade do que
      // já estava desenhado. "destination-out" apaga pelo canal alfa, então
      // o quadro anterior desbota sem pintar preto por cima — o que
      // escureceria a página inteira, já que este canvas fica sobre ela.
      ctx.globalCompositeOperation = 'destination-out';
      ctx.globalAlpha = 0.22; // quanto some por quadro: menos = rastro maior
      ctx.fillStyle = '#000';
      ctx.fillRect(0, 0, larg, alt);
      ctx.globalCompositeOperation = 'source-over';

      // Para de lançar antes do fim, pra última explosão ter tempo de
      // apagar em vez de sumir cortada.
      if(passado < DURACAO - 1600 && agora > proximoLancamento){
        lancar();
        if(passado < 400) lancar(); // uma saraivada no começo
        proximoLancamento = agora + 200 + Math.random() * 260;
      }

      for(let i = foguetes.length - 1; i >= 0; i--){
        const f = foguetes[i];
        f.y += f.vy;
        f.vy += 0.035; // desacelera subindo
        ctx.globalAlpha = 1;
        ctx.fillStyle = f.cor;
        ctx.beginPath();
        ctx.arc(f.x, f.y, 2.8, 0, Math.PI * 2);
        ctx.fill();
        // Estoura no alto do arco, ou ao chegar na altura sorteada.
        if(f.vy >= 0 || f.y <= f.alvo){
          estourar(f);
          foguetes.splice(i, 1);
        }
      }

      for(let i = fagulhas.length - 1; i >= 0; i--){
        const p = fagulhas[i];
        p.x += p.vx;
        p.y += p.vy;
        p.vy += 0.055;  // gravidade
        p.vx *= 0.985;  // ar
        p.vy *= 0.985;
        p.vida -= p.decai;
        if(p.vida <= 0){ fagulhas.splice(i, 1); continue; }
        ctx.globalAlpha = Math.max(p.vida, 0);
        ctx.fillStyle = p.cor;
        ctx.beginPath();
        ctx.arc(p.x, p.y, 2.6, 0, Math.PI * 2);
        ctx.fill();
      }

      if(passado > DURACAO && fagulhas.length === 0 && foguetes.length === 0){
        window.removeEventListener('resize', medir);
        tela.remove();
        return;
      }
      requestAnimationFrame(quadro);
    }

    requestAnimationFrame(quadro);
  }

  function ligarBotaoTelaCheia(escopo){
    const btn = escopo.querySelector('.vid-fullscreen-btn');
    if(!btn) return;
    btn.addEventListener('click', () => {
      const alvo = btn.closest('.vid-player-wrap');
      if(!alvo) return;
      if(alvo.classList.contains('is-tela-cheia-janela')){
        sairTelaCheiaNaJanela(alvo);
      } else if(document.fullscreenElement){
        document.exitFullscreen();
      } else if(document.fullscreenEnabled && alvo.requestFullscreen){
        alvo.requestFullscreen().catch(e => {
          console.warn('[tela cheia]', e.message);
          alternarTelaCheiaNaJanela(alvo);
        });
      } else {
        alternarTelaCheiaNaJanela(alvo);
      }
    });
  }

  // Dentro do Portal a Academy roda num iframe, e sem allow="fullscreen"
  // nesse iframe o navegador recusa qualquer tela cheia — o botão não
  // fazia nada. Nesse caso o player ocupa a janela inteira da Academy:
  // não é o monitor todo, mas é o máximo que o iframe deixa, e o aviso
  // "Você ainda está aí?" continua por cima porque está dentro da moldura.
  // Só muda o CSS: mover o iframe do YouTube no DOM recarregaria o vídeo.
  function alternarTelaCheiaNaJanela(alvo){
    alvo.classList.add('is-tela-cheia-janela');
    const btn = alvo.querySelector('.vid-fullscreen-btn');
    if(btn) btn.setAttribute('aria-label', 'Sair da tela cheia');
  }

  function sairTelaCheiaNaJanela(alvo){
    alvo.classList.remove('is-tela-cheia-janela');
    const btn = alvo.querySelector('.vid-fullscreen-btn');
    if(btn) btn.setAttribute('aria-label', 'Tela cheia');
  }

  // Esc sai da tela cheia na janela antes de qualquer outra coisa — sem
  // isto o Esc fechava o vídeo inteiro (o modal de Cursos escuta o Esc).
  document.addEventListener('keydown', (e) => {
    if(e.key !== 'Escape') return;
    const alvo = document.querySelector('.vid-player-wrap.is-tela-cheia-janela');
    if(!alvo) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    sairTelaCheiaNaJanela(alvo);
  }, true);

  // Rede de segurança: se o vídeo entrar em tela cheia sozinho, por um
  // caminho que não é o nosso botão (duplo clique no <video>, atalho do
  // navegador), a tela cheia passa pra moldura. Na tela cheia do próprio
  // vídeo nada da página aparece por cima — nem o "Você ainda está aí?".
  document.addEventListener('fullscreenchange', () => {
    const el = document.fullscreenElement;
    if(!el || el.classList.contains('vid-player-wrap')) return;
    const wrap = el.closest('.vid-player-wrap');
    if(!wrap) return;
    document.exitFullscreen()
      .then(() => wrap.requestFullscreen())
      .catch(() => {}); // navegador recusou a troca: fica fora da tela cheia
  });

  function onbSetWatchPct(mod, pct){
    onbMaxWatchedPct = Math.max(onbMaxWatchedPct, pct);
    const fill = document.getElementById(`vid-watch-fill-${mod.id}`);
    if(fill) fill.style.width = Math.min(onbMaxWatchedPct * 100, 100) + '%';

    enviarProgresso(mod.id, onbMaxWatchedPct);

    // Atividade do meio do vídeo ainda sem resposta (uma marcada bem no
    // fim, depois dos 95%) segura a conclusão: o servidor não concluiria
    // mesmo, e a trilha andaria pro próximo módulo com o vídeo no meio.
    if(onbAtividades && onbAtividades.pendentes() > 0) return;

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

  // O que aconteceu nesta "sessão" de quiz — zerado toda vez que as
  // perguntas reaparecem. "primeira" guarda o resultado da PRIMEIRA
  // tentativa de cada pergunta (não do retry de 1,2s que já existia), e
  // "acertadas" diz quais já foram vencidas, pra saber quando o módulo
  // inteiro acabou.
  let onbSessaoQuiz = { primeira: {}, acertadas: new Set() };

  function revealQuiz(mod){
    const quizEl = document.getElementById(`quiz-box-${mod.id}`);
    if(!quizEl) return;
    quizEl.hidden = false;
    onbSessaoQuiz = { primeira: {}, acertadas: new Set() }; // nova sessão

    // Só as perguntas do fim: as atividades (momento_seg) já foram
    // respondidas durante o vídeo.
    const perguntas = (mod.questions || []).filter(q => q.momento_seg == null);
    if(!perguntas.length){
      quizEl.hidden = true; // aula sem pergunta cadastrada
      return;
    }

    // Uma aula pode ter várias perguntas. Antes só a primeira era mostrada:
    // as outras ficavam gravadas no banco e ninguém nunca as via.
    //
    // Os textos e ids vêm do banco (question_text/option_text), e cada
    // opção carrega o id real — é o que o servidor usa pra conferir a
    // resposta, já que a alternativa certa não é mais enviada ao browser.
    quizEl.innerHTML = perguntas.map((q, i) => `
      <div class="quiz-pergunta" data-q="${q.id}">
        <h5>${perguntas.length > 1 ? `<span class="quiz-num">${i + 1}/${perguntas.length}</span> ` : ''}${q.question_text}</h5>
        <div class="quiz-options">
          ${q.options.map(opt => `
            <button type="button" class="quiz-option" data-option-id="${opt.id}">
              <span>${opt.option_text}</span>
            </button>
          `).join('')}
        </div>
        <div class="quiz-feedback" id="quiz-feedback-${mod.id}-${q.id}" hidden></div>
      </div>
    `).join('');

    perguntas.forEach(q => {
      const bloco = quizEl.querySelector(`.quiz-pergunta[data-q="${q.id}"]`);
      bloco.querySelectorAll('.quiz-option').forEach(btn => {
        btn.addEventListener('click', () => onbAnswerQuestion(mod, q, btn, bloco, perguntas));
      });
    });
  }

  async function onbAnswerQuestion(mod, question, btn, bloco, perguntas){
    const allOptions = bloco.querySelectorAll('.quiz-option');
    const feedbackEl = document.getElementById(`quiz-feedback-${mod.id}-${question.id}`);

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
    // Por pergunta, não por módulo: com várias, a segunda em diante nunca
    // seria registrada se a marca fosse uma só pro módulo inteiro.
    if(!(question.id in onbSessaoQuiz.primeira)){
      onbSessaoQuiz.primeira[question.id] = isCorrect;
    }

    if(isCorrect){
      btn.classList.add('correct');
      btn.insertAdjacentHTML('beforeend', '<i class="fa-solid fa-check opt-icon" aria-hidden="true"></i>');
      feedbackEl.hidden = false;
      feedbackEl.className = 'quiz-feedback ok';

      onbSessaoQuiz.acertadas.add(question.id);
      const faltam = perguntas.filter(q => !onbSessaoQuiz.acertadas.has(q.id)).length;

      if(faltam > 0){
        // Módulo só conclui quando todas forem acertadas — senão a primeira
        // certa liberaria o próximo e as outras não valeriam nada.
        feedbackEl.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Certo! '
          + (faltam === 1 ? 'Falta uma pergunta.' : `Faltam ${faltam} perguntas.`);
        return;
      }

      feedbackEl.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Rota concluída. Você já sabe navegar por aqui.';

      // O módulo conta como "acertou de primeira" só se TODAS as perguntas
      // foram certas já na primeira tentativa desta sessão. É o que mantém
      // a exigência de 75% do baú com o mesmo peso de antes, quando cada
      // módulo tinha uma pergunta só.
      onbProgress.firstTry[mod.id] = perguntas.every(q => onbSessaoQuiz.primeira[q.id] === true);

      if(!onbProgress.completed.includes(mod.id)){
        onbProgress.completed.push(mod.id);
      }
      saveOnboardingProgress(onbProgress);
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
  // ---------- Departamentos (os cartões da seção "Cursos") ----------
  // Vêm do banco. Eram 22 blocos escritos à mão no index.html: criar um
  // departamento pelo painel não fazia cartão aparecer, e trocar o nome no
  // banco não mudava o que estava escrito na tela.
  let areas = [];

  function renderAreas(){
    const grid = document.getElementById('areasGrid');
    if(!grid) return;

    if(!areas.length){
      grid.innerHTML = '<p class="areas-vazio">Nenhum departamento tem vídeo publicado ainda.</p>';
      return;
    }

    grid.innerHTML = areas.map(a => `
      <button type="button" class="area-card" data-area="${escaparHtml(a.slug)}">
        <div class="area-icon"><i class="fa-solid ${escaparHtml(a.icon)}" aria-hidden="true"></i></div>
        <h4>${escaparHtml(a.name)}</h4>
        <p>${escaparHtml(a.descricao || '')}</p>
        ${a.total_cursos === 0 ? '<span class="area-sem-video">sem vídeo — só você vê</span>' : ''}
      </button>
    `).join('');

    renderProgressPanel(); // recoloca a tarjinha de progresso nos cartões novos
  }

  function escaparHtml(s){
    return String(s == null ? '' : s).replace(/[&<>"']/g, ch => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[ch]);
  }

  async function carregarAreas(){
    try{
      const r = await apiGet('api/areas/list.php');
      areas = r.areas || [];
      renderAreas();
    }catch(e){
      const grid = document.getElementById('areasGrid');
      if(grid) grid.innerHTML = `<p class="areas-vazio">Não foi possível carregar os departamentos: ${escaparHtml(e.message)}</p>`;
    }
  }

  // O painel de admin vive noutro escopo e precisa redesenhar os cartões
  // logo depois de salvar um departamento — senão o nome novo só apareceria
  // ao recarregar a página, que é justamente o problema que ele resolve.
  window.mseRecarregarAreas = carregarAreas;

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
    await carregarAreas();
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
    modalState.acertadas = new Set();
    // Sem zerar, depois de um vídeo chegar a 100% o progresso do próximo
    // nunca era enviado na mesma visita (a trilha já zerava, o catálogo não).
    ultimoPctEnviado = -1;
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
        // controlslist="nofullscreen": a tela cheia do próprio <video> não
        // deixa nada da página por cima, nem o "Você ainda está aí?". No
        // lugar entra o botão da Academy, que expande a moldura inteira.
        iniciarSessaoPresenca(course.id);
        wrap.classList.add('tem-controles');
        wrap.innerHTML = `
          <video src="${detalhe.video_url}" controls controlslist="nofullscreen" playsinline style="width:100%;border-radius:12px"></video>
          <button type="button" class="vid-fullscreen-btn" aria-label="Tela cheia">
            <i class="fa-solid fa-display" aria-hidden="true"></i>
          </button>
        `;
        ligarBotaoTelaCheia(wrap);
        const v = wrap.querySelector('video');
        modalState.presenca = presencaNoVideo(wrap, v, course.id);
        if(!jaConcluiu) modalState.atividades = ligarAtividadesNoVideo(wrap, course.questions, controlesDoVideo(v));

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
        v.addEventListener('ended', () => { enviarProgresso(course.id, 1); encerrarSessaoPresenca(); showBonusQuiz(); });
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
      // Tela cheia sem sair da Academy. O botão é nosso, não o do YouTube:
      // o do YouTube põe o PLAYER em tela cheia, e aí o YouTube mostra a
      // barra dele de volta — controls:0 deixaria de valer e daria pra
      // adiantar o vídeo justamente ali. O nosso expande a moldura inteira
      // (.vid-player-wrap), então os controles da Academy vão junto e a
      // barra do YouTube continua escondida.
      //
      // Depois de concluído também: a tela cheia do próprio YouTube não
      // deixa nada da Academy por cima, nem o "Você ainda está aí?".
      if(jaConcluiu) wrap.classList.add('tem-controles');
      wrap.insertAdjacentHTML('beforeend', `
        <button type="button" class="vid-fullscreen-btn" aria-label="Tela cheia">
          <i class="fa-solid fa-display" aria-hidden="true"></i>
        </button>
      ` + (jaConcluiu ? '' : marcacaoControlesDeRevisao('catalog')));
      ligarBotaoTelaCheia(wrap);

      iniciarSessaoPresenca(course.id);
      loadYouTubeApi().then(() => {
        modalState.player = new YT.Player('catalog-yt-player', {
          videoId: detalhe.youtube_id,
          playerVars: jaConcluiu
            ? { controls: 1, modestbranding: 1, rel: 0, fs: 0 }
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
              if(e.data === YT.PlayerState.ENDED){ enviarProgresso(course.id, 1); encerrarSessaoPresenca(); showBonusQuiz(); }
            }
          }
        });
        modalState.presenca = presencaNoYouTube(wrap, modalState.player, course.id);
        if(!jaConcluiu) modalState.atividades = ligarAtividadesNoVideo(wrap, course.questions, controlesDoYouTube(modalState.player));
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

    // Só as perguntas do fim: as atividades já apareceram durante o vídeo.
    const perguntas = (course.questions || []).filter(q => q.momento_seg == null);
    if(!perguntas.length) return; // aula sem pergunta do fim

    // Uma aula pode ter várias. Antes só a primeira aparecia, e as outras
    // ficavam gravadas sem ninguém nunca ver.
    container.innerHTML = `
      <div class="quiz-box">
        <h5>${perguntas.length > 1
          ? `Perguntas rápidas (opcional, valem +10 pontos cada)`
          : 'Pergunta rápida (opcional, vale +10 pontos)'}</h5>
        ${perguntas.map((q, i) => `
          <div class="quiz-pergunta" data-q="${q.id}">
            <p class="quiz-enunciado">${perguntas.length > 1 ? `<span class="quiz-num">${i + 1}/${perguntas.length}</span> ` : ''}${q.question_text}</p>
            <div class="quiz-options">
              ${q.options.map(opt => `
                <button type="button" class="quiz-option" data-option-id="${opt.id}">
                  <span>${opt.option_text}</span>
                </button>
              `).join('')}
            </div>
            <div class="quiz-feedback" id="catalogQuizFeedback-${q.id}" hidden></div>
          </div>
        `).join('')}
      </div>
    `;

    perguntas.forEach(q => {
      const bloco = container.querySelector(`.quiz-pergunta[data-q="${q.id}"]`);
      bloco.querySelectorAll('.quiz-option').forEach(btn => {
        btn.addEventListener('click', () => answerCatalogQuiz(q, btn, bloco));
      });
    });
  }

  async function answerCatalogQuiz(question, btn, bloco){
    const allOptions = bloco.querySelectorAll('.quiz-option');
    const feedbackEl = document.getElementById('catalogQuizFeedback-' + question.id);

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

      // +10 por pergunta distinta acertada, e só uma vez cada: sem o
      // registro do que já foi acertado, reabrir a aula e responder de novo
      // viraria uma máquina de pontos.
      modalState.acertadas = modalState.acertadas || new Set();
      const primeiraVezNesta = !modalState.acertadas.has(question.id);
      modalState.acertadas.add(question.id);

      let mudou = false;
      if(!catalogProgress.completed.includes(course.id)){
        catalogProgress.completed.push(course.id);
        mudou = true;
      }
      if(primeiraVezNesta){
        catalogProgress.points += 10;
        mudou = true;
      }
      if(mudou) saveCatalogProgress(catalogProgress);
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
    if(modalState.presenca){ modalState.presenca.parar(); modalState.presenca = null; }
    if(modalState.atividades){ modalState.atividades.parar(); modalState.atividades = null; }
    encerrarSessaoPresenca(); // fechou o vídeo: check-out
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

  // ---------- Editor de alternativas ----------
  // Usado nos dois lugares que escrevem pergunta: o cadastro do vídeo e o
  // painel de pergunta de uma aula já criada. Antes eram três campos fixos
  // escritos no HTML — quem precisasse de uma quarta alternativa não tinha
  // como. Os dois endpoints já aceitavam qualquer quantidade; o limite era
  // só da tela.
  const QUIZ_MIN_OPCOES = 2;  // com uma só, não há o que escolher
  const QUIZ_MAX_OPCOES = 6;  // acima disso a lista de rádios fica ilegível

  function criarEditorDeOpcoes(container, grupo, iniciais){
    let opcoes = (iniciais && iniciais.length ? iniciais : [])
      .map(o => ({ text: o.text || '', is_correct: !!o.is_correct }));

    // Começa com três, que era o que o formulário tinha antes.
    while(opcoes.length < 3) opcoes.push({ text: '', is_correct: false });
    if(!opcoes.some(o => o.is_correct)) opcoes[0].is_correct = true;

    // Lê da tela antes de redesenhar, senão o que a pessoa acabou de
    // digitar some ao adicionar ou tirar uma linha.
    function sincronizar(){
      const textos = container.querySelectorAll('.quiz-opcao-texto');
      const marcada = container.querySelector('input[type="radio"]:checked');
      textos.forEach((inp, i) => { if(opcoes[i]) opcoes[i].text = inp.value; });
      opcoes.forEach((o, i) => { o.is_correct = !!marcada && Number(marcada.value) === i; });
    }

    function desenhar(){
      const podeRemover = opcoes.length > QUIZ_MIN_OPCOES;
      container.innerHTML = opcoes.map((o, i) => `
        <div class="admin-quiz-option">
          <input type="radio" name="${grupo}" value="${i}" ${o.is_correct ? 'checked' : ''}
                 aria-label="Marcar a opção ${i + 1} como correta">
          <input type="text" class="quiz-opcao-texto" maxlength="300" value="${esc(o.text)}"
                 placeholder="Opção ${i + 1}${i === 0 ? ' (marque a certa ao lado)' : ''}">
          ${podeRemover ? `<button type="button" class="quiz-opcao-remover" data-i="${i}"
                   title="Tirar esta opção" aria-label="Tirar a opção ${i + 1}">&times;</button>` : ''}
        </div>
      `).join('') + (opcoes.length < QUIZ_MAX_OPCOES
        ? '<button type="button" class="quiz-opcao-add">+ Adicionar opção</button>'
        : `<p class="admin-field-hint">Máximo de ${QUIZ_MAX_OPCOES} opções.</p>`);

      const add = container.querySelector('.quiz-opcao-add');
      if(add) add.addEventListener('click', () => {
        sincronizar();
        opcoes.push({ text: '', is_correct: false });
        desenhar();
        // Foca a linha nova: sem isso a pessoa clica e tem que ir procurar
        // onde digitar.
        const campos = container.querySelectorAll('.quiz-opcao-texto');
        campos[campos.length - 1].focus();
      });

      container.querySelectorAll('.quiz-opcao-remover').forEach(btn => {
        btn.addEventListener('click', () => {
          sincronizar();
          opcoes.splice(Number(btn.dataset.i), 1);
          // Se a removida era a correta, não sobra nenhuma marcada e o
          // servidor recusaria. A primeira assume.
          if(!opcoes.some(o => o.is_correct)) opcoes[0].is_correct = true;
          desenhar();
        });
      });
    }

    desenhar();

    return {
      // Opção em branco é linha não preenchida, não erro: sai fora.
      ler(){
        sincronizar();
        return opcoes
          .map(o => ({ text: o.text.trim(), is_correct: o.is_correct }))
          .filter(o => o.text !== '');
      },
      limpar(){
        opcoes = [
          { text: '', is_correct: true },
          { text: '', is_correct: false },
          { text: '', is_correct: false },
        ];
        desenhar();
      },
    };
  }

  // O cadastro de vídeo usa um editor só, recriado a cada abertura do modal.
  let editorOpcoesVideo = null;

  function openVideoModal(){
    fillAreaSelect();
    editorOpcoesVideo = criarEditorDeOpcoes(
      document.getElementById('videoModalOpcoes'), 'videoModalCorreta', null
    );
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
        body.quiz_options = editorOpcoesVideo ? editorOpcoesVideo.ler() : [];
      }

      Object.assign(body, lerCamposAuditoriaDoModal());

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
        if(editorOpcoesVideo) editorOpcoesVideo.limpar();
        limparCamposAuditoriaDoModal();
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

  // Só manda o que foi preenchido: campo em branco nem vai, e assim o
  // cadastro continua funcionando antes da migração 020 rodar no banco.
  function lerCamposAuditoriaDoModal(){
    const campos = {};
    const tipo = document.getElementById('videoModalTipoTreinamento');
    if(!tipo) return campos;
    const normas = Array.from(document.querySelectorAll('#videoModalNormas input:checked')).map(i => i.value);
    const texto = {
      tipo_treinamento: tipo.value,
      instrutor: document.getElementById('videoModalInstrutor').value.trim(),
      conteudo_programatico: document.getElementById('videoModalConteudo').value.trim(),
      assuntos: document.getElementById('videoModalAssuntos').value.trim(),
    };
    Object.entries(texto).forEach(([k, v]) => { if(v) campos[k] = v; });
    if(normas.length) campos.normas = normas;
    return campos;
  }
  function limparCamposAuditoriaDoModal(){
    if(!document.getElementById('videoModalTipoTreinamento')) return;
    ['videoModalTipoTreinamento', 'videoModalInstrutor', 'videoModalConteudo', 'videoModalAssuntos']
      .forEach(id => { document.getElementById(id).value = ''; });
    document.querySelectorAll('#videoModalNormas input').forEach(i => { i.checked = false; });
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
  // ---------- Modal de "Departamentos" ----------
  // Criar, renomear e trocar o ícone dos cartões da seção "Cursos".
  //
  // Só os ícones que a fonte do site realmente tem. O Font Awesome daqui é
  // um recorte embutido no style.css, não a biblioteca inteira: um nome que
  // exista no site do Font Awesome mas não neste recorte não desenha nada e
  // deixa um buraco no cartão. Esta lista é a mesma do servidor, que recusa
  // qualquer ícone fora dela.
  const ICONES_DE_AREA = [
    'fa-folder-open', 'fa-house', 'fa-building', 'fa-warehouse', 'fa-boxes-stacked',
    'fa-truck', 'fa-route', 'fa-diagram-project', 'fa-helmet-safety', 'fa-circle-exclamation',
    'fa-briefcase-medical',
    'fa-user', 'fa-user-plus', 'fa-handshake', 'fa-credit-card', 'fa-receipt',
    'fa-file-contract', 'fa-file-invoice', 'fa-file-lines', 'fa-file-signature',
    'fa-chart-line', 'fa-medal', 'fa-star', 'fa-book', 'fa-display', 'fa-magnifying-glass',
  ];

  function openAreasModal(){
    document.getElementById('areasModalOverlay').hidden = false;
    loadAreasAdmin();
  }
  function closeAreasModal(){
    document.getElementById('areasModalOverlay').hidden = true;
  }

  function esc(s){
    return String(s == null ? '' : s).replace(/[&<>"']/g, ch => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[ch]);
  }

  function seletorDeIcone(iconeAtual){
    return `<div class="area-icon-picker">` + ICONES_DE_AREA.map(ic => `
      <button type="button" class="area-icon-opt${ic === iconeAtual ? ' is-ativo' : ''}"
              data-icone="${ic}" title="${ic.replace('fa-', '')}" aria-label="${ic.replace('fa-', '')}">
        <i class="fa-solid ${ic}" aria-hidden="true"></i>
      </button>
    `).join('') + `</div>`;
  }

  // Um formulário só, usado pra criar e pra editar. area=null cria.
  function formularioDeArea(area){
    const criando = !area;
    return `
      <div class="area-form" data-id="${criando ? '' : area.id}">
        <label>Nome</label>
        <input type="text" class="area-form-nome" value="${criando ? '' : esc(area.name)}"
               placeholder="Ex: Segurança do Trabalho" maxlength="120">
        <label>Descrição <span class="admin-field-hint-inline">(a frase embaixo do nome no cartão)</span></label>
        <input type="text" class="area-form-desc" value="${criando ? '' : esc(area.descricao || '')}"
               placeholder="Ex: Normas, EPIs e procedimentos de segurança." maxlength="255">
        <label>Ícone</label>
        ${seletorDeIcone(criando ? 'fa-folder-open' : area.icon)}
        <div class="area-form-acoes">
          <button type="button" class="admin-modal-submit area-form-salvar">${criando ? 'Criar departamento' : 'Salvar'}</button>
          <button type="button" class="area-form-cancelar">Cancelar</button>
        </div>
        <div class="admin-modal-feedback area-form-feedback" hidden></div>
      </div>
    `;
  }

  async function loadAreasAdmin(){
    const body = document.getElementById('areasModalBody');
    body.innerHTML = '<p>Carregando...</p>';
    try{
      const data = await apiFetch('api/areas/list.php');
      const lista = data.areas || [];

      body.innerHTML = `
        <button type="button" class="area-novo-btn" id="areaNovoBtn">+ Novo departamento</button>
        <div id="areaNovoWrap"></div>
        <ul class="areas-admin-list">
          ${lista.map(a => `
            <li class="areas-admin-item" data-id="${a.id}">
              <div class="areas-admin-info">
                <div class="area-icon area-icon-mini"><i class="fa-solid ${esc(a.icon)}" aria-hidden="true"></i></div>
                <div>
                  <div class="areas-admin-nome">${esc(a.name)}</div>
                  <div class="areas-admin-meta">
                    ${a.total_cursos === 0
                      ? 'nenhum vídeo — não aparece pra quem não é admin'
                      : a.total_cursos + (a.total_cursos === 1 ? ' vídeo' : ' vídeos')}
                  </div>
                </div>
              </div>
              <div class="areas-admin-acoes">
                <button type="button" class="areas-admin-editar" data-editar="${a.id}">Editar</button>
                <button type="button" class="areas-admin-excluir" data-excluir="${a.id}">Excluir</button>
              </div>
            </li>
            <li class="areas-admin-form" data-form="${a.id}" hidden></li>
          `).join('')}
        </ul>
      `;

      // Criar
      document.getElementById('areaNovoBtn').addEventListener('click', () => {
        const wrap = document.getElementById('areaNovoWrap');
        if(wrap.innerHTML){ wrap.innerHTML = ''; return; }
        wrap.innerHTML = formularioDeArea(null);
        ligarFormularioDeArea(wrap.querySelector('.area-form'), null);
        wrap.querySelector('.area-form-nome').focus();
      });

      // Excluir — em dois passos. O primeiro clique só pergunta ao servidor
      // o que a exclusão levaria junto e mostra na tela; o segundo é que
      // apaga. Confirmação genérica ("tem certeza?") não ajudaria: o que
      // pesa aqui são as pessoas que ficam sem departamento e as regras de
      // cargo que somem, e isso não está à vista em lugar nenhum.
      body.querySelectorAll('[data-excluir]').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = Number(btn.dataset.excluir);
          const linha = body.querySelector(`[data-form="${id}"]`);
          linha.hidden = false;
          linha.innerHTML = '<div class="area-form"><p>Verificando...</p></div>';

          try{
            const r = await apiFetch('api/admin/areas/excluir.php', {
              method: 'POST', body: JSON.stringify({ id })
            });
            linha.innerHTML = `
              <div class="area-form area-form-perigo">
                <p class="area-excluir-titulo">${esc(r.message)}</p>
                ${r.consequencias && r.consequencias.length ? `
                  <ul class="area-excluir-lista">
                    ${r.consequencias.map(c => `<li>${esc(c)}</li>`).join('')}
                  </ul>` : ''}
                <div class="area-form-acoes">
                  <button type="button" class="area-excluir-confirmar">Excluir mesmo assim</button>
                  <button type="button" class="area-form-cancelar">Cancelar</button>
                </div>
              </div>
            `;
            linha.querySelector('.area-form-cancelar').addEventListener('click', () => {
              linha.hidden = true; linha.innerHTML = '';
            });
            const ok = linha.querySelector('.area-excluir-confirmar');
            ok.addEventListener('click', async () => {
              ok.disabled = true;
              try{
                await apiFetch('api/admin/areas/excluir.php', {
                  method: 'POST', body: JSON.stringify({ id, confirmar: true })
                });
                await loadAreasAdmin();
                if(typeof window.mseRecarregarAreas === 'function') window.mseRecarregarAreas();
              }catch(e){
                linha.querySelector('.area-excluir-titulo').textContent = e.message;
                ok.disabled = false;
              }
            });
          }catch(e){
            // O caso mais comum aqui é o departamento ainda ter vídeos, e a
            // mensagem do servidor já diz o que fazer.
            linha.innerHTML = `
              <div class="area-form area-form-perigo">
                <p class="area-excluir-titulo">${esc(e.message)}</p>
                <div class="area-form-acoes">
                  <button type="button" class="area-form-cancelar">Fechar</button>
                </div>
              </div>`;
            linha.querySelector('.area-form-cancelar').addEventListener('click', () => {
              linha.hidden = true; linha.innerHTML = '';
            });
          }
        });
      });

      // Editar
      body.querySelectorAll('[data-editar]').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = Number(btn.dataset.editar);
          const area = lista.find(a => a.id === id);
          const linha = body.querySelector(`[data-form="${id}"]`);
          if(!linha.hidden){ linha.hidden = true; linha.innerHTML = ''; return; }
          linha.hidden = false;
          linha.innerHTML = formularioDeArea(area);
          ligarFormularioDeArea(linha.querySelector('.area-form'), area);
          linha.querySelector('.area-form-nome').focus();
        });
      });
    }catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${esc(e.message)}</p>`;
    }
  }

  function ligarFormularioDeArea(form, area){
    let icone = area ? area.icon : 'fa-folder-open';

    form.querySelectorAll('.area-icon-opt').forEach(opt => {
      opt.addEventListener('click', () => {
        icone = opt.dataset.icone;
        form.querySelectorAll('.area-icon-opt').forEach(o => o.classList.toggle('is-ativo', o === opt));
      });
    });

    form.querySelector('.area-form-cancelar').addEventListener('click', () => {
      const pai = form.parentElement;
      pai.innerHTML = '';
      if(pai.hasAttribute('data-form')) pai.hidden = true;
    });

    const salvar = form.querySelector('.area-form-salvar');
    salvar.addEventListener('click', async () => {
      const nome = form.querySelector('.area-form-nome').value.trim();
      const desc = form.querySelector('.area-form-desc').value.trim();
      const aviso = form.querySelector('.area-form-feedback');

      const mostrar = (texto, erro) => {
        aviso.hidden = false;
        aviso.textContent = texto;
        aviso.classList.toggle('erro', !!erro);
      };

      if(!nome){ mostrar('Escreva o nome do departamento.', true); return; }

      salvar.disabled = true;
      try{
        const r = await apiFetch('api/admin/areas/salvar.php', {
          method: 'POST',
          body: JSON.stringify({ id: area ? area.id : 0, name: nome, descricao: desc, icon: icone })
        });
        mostrar(r.message || 'Salvo.', false);
        // Recarrega a lista do painel e os cartões da tela de uma vez: sem
        // isso, o nome novo só apareceria no cartão depois de recarregar a
        // página, que foi exatamente o problema que este painel resolve.
        await loadAreasAdmin();
        if(typeof window.mseRecarregarAreas === 'function') window.mseRecarregarAreas();
      }catch(e){
        mostrar(e.message, true);
        salvar.disabled = false;
      }
    });
  }

  // ================================================================
  // ---------- Busca de treinamentos (substituiu "Gerenciar aulas") ----------
  // ================================================================
  // Tela de consulta pra auditoria (ISO 9001, 14001 e 45001): cada vídeo é
  // um treinamento online, com filtros, lista de presença automática e
  // exportação em CSV e PDF. As ações que a lista antiga tinha continuam em
  // cada linha — presença, editar, perguntas e atividades, áreas, arquivar
  // e excluir — e abrem numa janela por cima, sem perder os filtros.
  // modo: 'gestao' (botão Treinamentos: gerenciar os vídeos) ou
  // 'relatorios' (botão Relatórios: o que eram Acessos, Quem assistiu e
  // Relatório por pessoa, numa tela só).
  const trnEstado = { modo: 'gestao', totalIntegracao: 0, lista: [], pessoas: [], visao: 'treinamentos', tipos: [], normas: [], auditoriaAtiva: true, timer: null, pedido: 0 };

  // A janela das ações de uma aula. Antes ela era a própria tela de
  // "Gerenciar aulas"; agora serve a todas as ações da Busca.
  function abrirDialogoAula(titulo, subtitulo, largo){
    document.getElementById('aulasModalTitle').textContent = titulo;
    const sub = document.getElementById('aulasModalSubtitle');
    sub.textContent = subtitulo || '';
    sub.hidden = !subtitulo;
    document.querySelector('#aulasModalOverlay .admin-modal').classList.toggle('admin-modal-xl', !!largo);
    document.getElementById('aulasModalOverlay').hidden = false;
    return document.getElementById('aulasModalBody');
  }
  function closeAulasModal(){
    document.getElementById('aulasModalOverlay').hidden = true;
    document.getElementById('aulasModalBody').innerHTML = '';
  }

  function openTreinamentosTela(modo){
    trnEstado.modo = modo === 'relatorios' ? 'relatorios' : 'gestao';
    const relatorios = trnEstado.modo === 'relatorios';
    document.getElementById('trnTitulo').textContent = relatorios ? 'Relatórios de treinamento' : 'Busca de treinamentos';
    document.querySelector('#treinamentosTela .trn-cabecalho p').textContent = relatorios
      ? 'Por pessoa e por treinamento · acessos, integração, presença, check-in/check-out e lista REH-002-F1'
      : 'Online · ISO 9001, 14001 e 45001 · Evidência por check-in e check-out';
    // Gerenciar vídeos é só por treinamento; relatórios abrem por pessoa.
    document.getElementById('trnVisao').hidden = !relatorios;
    trnMudarVisao(relatorios ? 'pessoas' : 'treinamentos');
    document.getElementById('treinamentosTela').hidden = false;
    document.documentElement.classList.add('trn-aberta');
    carregarTreinamentos();
    setTimeout(() => document.getElementById('trnBusca').focus(), 0);
  }
  function trnMudarVisao(visao){
    trnEstado.visao = visao;
    document.querySelectorAll('#trnVisao button').forEach(x => x.setAttribute('aria-pressed', String(x.dataset.visao === visao)));
    const porPessoa = visao === 'pessoas';
    document.getElementById('trnTabelaTreinamentos').hidden = porPessoa;
    document.getElementById('trnTabelaPessoas').hidden = !porPessoa;
    document.getElementById('trnNovoVideo').hidden = porPessoa || trnEstado.modo === 'relatorios';
    document.getElementById('trnAtualizarPortal').hidden = !porPessoa;
  }


  // Cargo, CPF e departamento vêm da ficha do Portal, mas o login só grava
  // isso no momento em que a pessoa entra. Aqui atualiza todo mundo, em
  // lotes (a API leva até 5s por pessoa), e mostra o motivo se a API falhar.
  async function atualizarPeloPortal(){
    const btn = document.getElementById('trnAtualizarPortal');
    const fb = document.getElementById('trnPortalFeedback');
    const texto = btn.textContent;
    btn.disabled = true;
    fb.hidden = false;
    fb.className = 'admin-modal-feedback';
    const junta = { atualizados: [], sem_ficha: [], nome_diferente: [] };
    let aposId = 0;
    try{
      do{
        const r = await apiFetch('api/admin/treinamentos/atualizar_portal.php', {
          method: 'POST', body: JSON.stringify({ apos_id: aposId }),
        });
        Object.keys(junta).forEach(k => junta[k].push(...(r[k] || [])));
        btn.textContent = `Atualizando... ${r.processados}/${r.total}`;
        aposId = r.proximo_apos_id;
      } while(aposId !== null && aposId !== undefined);

      const partes = [`${junta.atualizados.length} ${junta.atualizados.length === 1 ? 'pessoa atualizada' : 'pessoas atualizadas'}.`];
      if(junta.sem_ficha.length) partes.push(`Sem ficha no Portal: ${junta.sem_ficha.join(', ')}.`);
      if(junta.nome_diferente.length) partes.push(`Não atualizadas porque o nome não bateu (sem CPF pra confirmar): ${junta.nome_diferente.join('; ')}.`);
      fb.className = 'admin-modal-feedback ok';
      fb.textContent = partes.join(' ');
      carregarTreinamentos();
    }catch(e){
      fb.className = 'admin-modal-feedback erro';
      fb.textContent = 'Não foi possível atualizar pelo Portal: ' + e.message;
    }finally{
      btn.disabled = false;
      btn.textContent = texto;
    }
  }

  function closeTreinamentosTela(){
    document.getElementById('treinamentosTela').hidden = true;
    document.documentElement.classList.remove('trn-aberta');
  }

  function trnFiltros(){
    const ativo = document.querySelector('#trnModalidade [aria-pressed="true"]');
    return {
      q: document.getElementById('trnBusca').value.trim(),
      modalidade: ativo ? ativo.dataset.modalidade : '',
      tipo: document.getElementById('trnTipo').value,
      norma: document.getElementById('trnNorma').value,
      data_de: document.getElementById('trnDataDe').value,
      data_ate: document.getElementById('trnDataAte').value,
    };
  }
  function trnQuery(extra){
    const p = new URLSearchParams();
    Object.entries(Object.assign(trnFiltros(), extra || {})).forEach(([k, v]) => { if(v) p.set(k, v); });
    return p.toString();
  }

  // "2026-09-17 11:54:44" (como vem do MySQL) → "17/09/2026"
  function trnFmtData(s){
    if(!s) return '';
    const [d] = String(s).split(' ');
    const [a, m, dia] = d.split('-');
    return dia && m && a ? `${dia}/${m}/${a}` : s;
  }
  function trnFmtDataHora(s){
    if(!s) return '';
    const hora = String(s).split(' ')[1] || '';
    return trnFmtData(s) + (hora ? ' ' + hora.slice(0, 5) : '');
  }
  function trnFmtCpf(c){
    const d = String(c || '').replace(/\D/g, '');
    return d.length === 11 ? `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}` : (c || '');
  }
  function trnFmtTempo(seg){
    if(seg == null) return '';
    const m = Math.floor(seg / 60), s = seg % 60;
    return `${m}min${s ? ' ' + String(s).padStart(2, '0') + 's' : ''}`;
  }
  const TRN_STATUS = { concluido: 'Concluído', em_andamento: 'Em andamento', nao_iniciado: 'Só abriu' };
  function trnStatus(s){ return TRN_STATUS[s] || s || ''; }

  async function carregarTreinamentos(){
    if(document.getElementById('treinamentosTela').hidden) return;
    // Primeira carga sempre pela lista de treinamentos: é ela que traz as
    // opções de Tipo e Norma pros filtros.
    if(trnEstado.visao === 'pessoas' && trnEstado.tipos.length) return carregarPessoas();
    const pedido = ++trnEstado.pedido;
    const contagem = document.getElementById('trnContagem');
    contagem.textContent = 'Carregando...';
    try{
      const d = await apiFetch('api/admin/treinamentos/busca.php?' + trnQuery());
      if(pedido !== trnEstado.pedido) return; // resposta atrasada de uma busca anterior
      trnEstado.lista = d.treinamentos || [];
      trnEstado.auditoriaAtiva = d.auditoria_ativa !== false;
      document.getElementById('trnAvisoMigracao').hidden = trnEstado.auditoriaAtiva;

      // Opções dos filtros vêm do servidor (a mesma lista que ele valida).
      [['trnTipo', d.tipos || [], 'tipos'], ['trnNorma', d.normas || [], 'normas']].forEach(([id, opcoes, chave]) => {
        if(trnEstado[chave].join('|') === opcoes.join('|')) return;
        trnEstado[chave] = opcoes;
        const sel = document.getElementById(id);
        const atual = sel.value;
        sel.length = 1; // mantém o "Todos/Todas"
        opcoes.forEach(o => sel.add(new Option(o, o)));
        sel.value = atual;
      });

      if(trnEstado.visao === 'pessoas') return carregarPessoas();
      renderTreinamentos();
    }catch(e){
      if(pedido !== trnEstado.pedido) return;
      contagem.textContent = 'Não foi possível carregar: ' + e.message;
      document.getElementById('trnCorpo').innerHTML = '';
    }
  }

  // ---------- Relatório por pessoa ----------
  // Todo colaborador ativo, com quantos treinamentos fez dentro dos
  // filtros — inclusive quem fez zero: pra auditoria, saber quem NÃO fez
  // é tão importante quanto quem fez. A ficha de cada um lista os
  // treinamentos com a mesma evidência da lista de presença.
  async function carregarPessoas(){
    const pedido = ++trnEstado.pedido;
    const contagem = document.getElementById('trnContagem');
    contagem.textContent = 'Carregando...';
    try{
      const d = await apiFetch('api/admin/treinamentos/pessoas.php?' + trnQuery());
      if(pedido !== trnEstado.pedido) return;
      trnEstado.pessoas = d.pessoas || [];
      trnEstado.totalIntegracao = d.total_integracao || 0;
      renderPessoas();
    }catch(e){
      if(pedido !== trnEstado.pedido) return;
      contagem.textContent = 'Não foi possível carregar: ' + e.message;
      document.getElementById('trnCorpoPessoas').innerHTML = '';
    }
  }

  function renderPessoas(){
    const lista = trnEstado.pessoas;
    const fizeram = lista.filter(p => p.treinamentos > 0).length;
    document.getElementById('trnContagem').textContent =
      `${lista.length} ${lista.length === 1 ? 'pessoa' : 'pessoas'} · ${fizeram} com treinamento nos filtros`;
    const corpo = document.getElementById('trnCorpoPessoas');
    corpo.innerHTML = '';
    if(!lista.length){
      corpo.innerHTML = `<tr><td colspan="10" class="trn-vazio-lista">${
        trnFiltros().modalidade === 'presencial'
          ? 'Treinamentos presenciais ainda não são registrados na Academy.'
          : 'Ninguém encontrado com essa busca.'}</td></tr>`;
      return;
    }
    const vazio = '<span class="trn-vazio">—</span>';
    lista.forEach(p => {
      const tr = document.createElement('tr');
      if(!p.treinamentos) tr.className = 'is-sem-treinamento';
      tr.innerHTML = `
        <td><strong>${esc(p.nome)}</strong><span class="trn-sub">${esc(p.email || '')}</span></td>
        <td class="trn-nowrap">${p.cpf ? esc(trnFmtCpf(p.cpf)) : vazio}</td>
        <td>${p.cargo ? esc(p.cargo) : vazio}</td>
        <td>${p.departamento ? esc(p.departamento) : vazio}</td>
        <td>${p.acessos}</td>
        <td class="trn-nowrap">${trnEstado.totalIntegracao ? `${p.integracao_concluidos} de ${trnEstado.totalIntegracao}${p.integracao_concluidos >= trnEstado.totalIntegracao ? ' ✓' : ''}` : vazio}</td>
        <td>${p.treinamentos}</td>
        <td>${p.concluidos}</td>
        <td class="trn-nowrap">${p.ultimo_acesso ? esc(trnFmtData(p.ultimo_acesso)) : vazio}</td>
        <td class="trn-acoes"><div class="trn-acoes-grade">
          <button type="button" class="aulas-btn" data-acao="ficha">Ver ficha</button>
          <button type="button" class="aulas-btn" data-acao="reh" title="Lista de presença REH-002-F1 com todos os treinamentos da pessoa">Lista REH</button>
        </div></td>
      `;
      tr.querySelector('[data-acao="ficha"]').addEventListener('click', () => abrirFichaPessoa(p));
      const btnReh = tr.querySelector('[data-acao="reh"]');
      btnReh.addEventListener('click', () => trnComBotao(btnReh, () => baixarRehPessoa(p.user_id, trnFiltros())));
      corpo.appendChild(tr);
    });
  }

  function trnPessoaDadosHtml(p){
    return `
      <dl class="rel-dados trn-ficha-dados">
        <div><dt>Nome</dt><dd>${esc(p.nome)}</dd></div>
        <div><dt>CPF</dt><dd>${esc(p.cpf ? trnFmtCpf(p.cpf) : '—')}</dd></div>
        <div><dt>E-mail</dt><dd>${esc(p.email || '—')}</dd></div>
        <div><dt>Cargo</dt><dd>${esc(p.cargo || '—')}</dd></div>
        <div><dt>Departamento</dt><dd>${esc(p.departamento || '—')}</dd></div>
        <div><dt>Último acesso</dt><dd>${esc(p.ultimo_acesso ? trnFmtData(p.ultimo_acesso) : '—')}</dd></div>
      </dl>
    `;
  }

  // Coluna de sessões (check-ins do log) com o botão que abre o log
  // completo logo abaixo da linha. No PDF vai só o número.
  function trnSessoesHtml(r, userId, courseId){
    const vazio = '<span class="trn-vazio">—</span>';
    return `${r.sessoes ? r.sessoes + '×' : vazio}${r.ultimo_checkout_em ? `<span class="trn-sub">última saída ${esc(trnFmtDataHora(r.ultimo_checkout_em))}</span>` : ''}`
      + (userId ? `<button type="button" class="trn-link trn-ver-log" data-user="${userId}" data-course="${courseId}">Ver log</button>` : '');
  }

  const TRN_EVENTOS = { checkin: 'Check-in (abriu o vídeo)', checkout: 'Check-out (saiu)', presenca: 'Presença confirmada ("Estou aqui")' };

  // logDisponivel: a tabela do log existe no banco (migração 021). Sem
  // ela, nada é gravado; com ela, o log vazio só quer dizer que ninguém
  // abriu esse vídeo desde que a migração rodou.
  function trnLogHtml(eventos, comTreinamento, logDisponivel){
    if(!eventos.length){
      return logDisponivel === false
        ? '<p class="trn-vazio-lista">O log ainda não está sendo gravado: falta rodar a migração 021 (migrations/021_log_checkin_checkout.sql) no banco do site.</p>'
        : '<p class="trn-vazio-lista">Nenhum check-in ou check-out registrado ainda. O log é gravado a partir de quando a migração 021 rodou: acessos anteriores a ela não aparecem aqui — só a data de conclusão, nas colunas de check-in/check-out.</p>';
    }
    return `
      <table class="trn-tabela trn-tabela-log">
        <thead><tr><th>Data e hora</th>${comTreinamento ? '<th>Treinamento</th>' : ''}<th>Evento</th><th>% assistido</th><th>Navegador</th></tr></thead>
        <tbody>
          ${eventos.map(ev => `
            <tr>
              <td class="trn-nowrap">${esc(trnFmtDataHora(ev.em))}${ev.em ? ':' + esc(String(ev.em).slice(17, 19)) : ''}</td>
              ${comTreinamento ? `<td>${esc(ev.tema)}</td>` : ''}
              <td>${esc(TRN_EVENTOS[ev.evento] || ev.evento)}</td>
              <td>${ev.watched_pct != null ? Math.round(ev.watched_pct) + '%' : ''}</td>
              <td class="trn-navegador">${esc(trnNavegador(ev.navegador))}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  }

  // "Mozilla/5.0 (Windows NT 10.0...) Chrome/129..." → "Chrome · Windows"
  function trnNavegador(ua){
    if(!ua) return '';
    const nav = /Edg\//.test(ua) ? 'Edge' : /Chrome\//.test(ua) ? 'Chrome' : /Firefox\//.test(ua) ? 'Firefox' : /Safari\//.test(ua) ? 'Safari' : 'Outro';
    const so = /Android/.test(ua) ? 'Android' : /iPhone|iPad/.test(ua) ? 'iOS' : /Windows/.test(ua) ? 'Windows' : /Mac OS/.test(ua) ? 'Mac' : /Linux/.test(ua) ? 'Linux' : '';
    return so ? `${nav} · ${so}` : nav;
  }

  // Clique em "Ver log": abre (ou fecha) a linha com o log logo abaixo.
  function ligarBotoesLog(escopo){
    escopo.addEventListener('click', async (e) => {
      const btn = e.target.closest('.trn-ver-log');
      if(!btn) return;
      const tr = btn.closest('tr');
      if(tr.nextElementSibling && tr.nextElementSibling.classList.contains('trn-log-linha')){
        tr.nextElementSibling.remove();
        btn.textContent = 'Ver log';
        return;
      }
      const linha = document.createElement('tr');
      linha.className = 'trn-log-linha';
      linha.innerHTML = `<td colspan="${tr.children.length}"><p>Carregando log...</p></td>`;
      tr.insertAdjacentElement('afterend', linha);
      btn.textContent = 'Fechar log';
      try{
        const d = await apiFetch(`api/admin/treinamentos/log.php?user_id=${encodeURIComponent(btn.dataset.user)}&course_id=${encodeURIComponent(btn.dataset.course)}`);
        linha.firstElementChild.innerHTML = trnLogHtml(d.eventos || [], false, d.log_disponivel);
      }catch(err){
        linha.firstElementChild.innerHTML = `<p>Não foi possível carregar o log: ${esc(err.message)}</p>`;
      }
    });
  }

  function trnTabelaFichaHtml(treinamentos, userId){
    if(!treinamentos.length){
      return '<p class="trn-vazio-lista">Nenhum treinamento com esses filtros.</p>';
    }
    const vazio = '<span class="trn-vazio">—</span>';
    return `
      <div class="trn-tabela-wrap">
        <table class="trn-tabela trn-tabela-presenca">
          <thead><tr>
            <th>Treinamento</th><th>Tipo</th><th>Norma</th><th>Instrutor</th>
            <th title="Primeira vez que abriu o vídeo">Check-in</th><th title="Quando concluiu">Check-out</th>
            <th>Assistido</th><th title="Vezes que respondeu &quot;Estou aqui&quot; durante o vídeo">Presença confirmada</th>
            <th title="Perguntas e atividades acertadas">Acertos</th><th>Situação</th>
            <th title="Vezes que entrou no vídeo (check-ins do log)">Sessões</th>
          </tr></thead>
          <tbody>
            ${treinamentos.map(t => `
              <tr>
                <td><strong>${esc(t.tema)}</strong>${t.arquivado ? ' <span class="aulas-badge">arquivada</span>' : ''}<span class="trn-sub">Online</span></td>
                <td>${esc(t.tipo)}</td>
                <td>${t.normas.length ? esc(t.normas.join(' · ')) : vazio}</td>
                <td>${t.instrutor ? esc(t.instrutor) : vazio}</td>
                <td class="trn-nowrap">${t.checkin_em ? esc(trnFmtDataHora(t.checkin_em)) : vazio}</td>
                <td class="trn-nowrap">${t.checkout_em ? esc(trnFmtDataHora(t.checkout_em)) : vazio}</td>
                <td class="trn-nowrap">${Math.round(t.watched_pct)}%${t.tempo_assistido_seg != null ? `<span class="trn-sub">≈ ${esc(trnFmtTempo(t.tempo_assistido_seg))}</span>` : ''}</td>
                <td class="trn-nowrap">${t.confirmacoes_presenca}×${t.ultima_confirmacao_em ? `<span class="trn-sub">última ${esc(trnFmtDataHora(t.ultima_confirmacao_em))}</span>` : ''}</td>
                <td class="trn-nowrap">${t.total_perguntas ? `${t.acertos}/${t.total_perguntas}` : vazio}${t.tentativas > t.acertos ? `<span class="trn-sub">${t.tentativas} tentativas</span>` : ''}</td>
                <td><span class="watchers-status ${esc(t.status)}">${esc(trnStatus(t.status))}</span></td>
                <td class="trn-nowrap">${trnSessoesHtml(t, userId, t.id)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
  }

  async function abrirFichaPessoa(p){
    const body = abrirDialogoAula('Ficha de treinamentos', p.nome, true);
    body.innerHTML = '<p>Carregando...</p>';
    try{
      const f = trnFiltros();
      const q = new URLSearchParams({ user_id: p.user_id });
      ['tipo', 'norma', 'data_de', 'data_ate', 'modalidade'].forEach(k => { if(f[k]) q.set(k, f[k]); });
      const d = await apiFetch('api/admin/treinamentos/pessoas.php?' + q.toString());
      const pessoa = Object.assign({}, d.pessoa, { lista_treinamentos: d.treinamentos || [] });
      body.innerHTML = `
        <div class="admin-filtros">
          <span class="trn-ficha-resumo">${pessoa.lista_treinamentos.length} ${pessoa.lista_treinamentos.length === 1 ? 'treinamento' : 'treinamentos'} ·
            ${pessoa.lista_treinamentos.filter(t => t.status === 'concluido').length} concluídos</span>
          <button type="button" class="trn-btn" id="fichaCsv">Exportar CSV</button>
          <button type="button" class="trn-btn" id="fichaPdf">Baixar PDF</button>
          <button type="button" class="trn-btn" id="fichaReh">Lista REH-002-F1 (PDF)</button>
        </div>
        ${trnPessoaDadosHtml(pessoa)}
        ${trnTabelaFichaHtml(pessoa.lista_treinamentos, pessoa.user_id)}
      `;
      ligarBotoesLog(body);
      const nomeArquivo = 'ficha-' + (trnSlug(pessoa.nome) || pessoa.user_id);
      document.getElementById('fichaCsv').addEventListener('click', () =>
        trnBaixarCsv(`${nomeArquivo}-${new Date().toISOString().slice(0, 10)}.csv`, trnLinhasCsvPessoas([pessoa])));
      const btnFichaReh = document.getElementById('fichaReh');
      btnFichaReh.addEventListener('click', () => trnComBotao(btnFichaReh, () => baixarRehPessoa(pessoa.user_id, f)));
      const btnFichaPdf = document.getElementById('fichaPdf');
      btnFichaPdf.addEventListener('click', () => trnComBotao(btnFichaPdf, async () => {
        const qLog = new URLSearchParams({ user_id: pessoa.user_id });
        if(f.data_de) qLog.set('data_de', f.data_de);
        if(f.data_ate) qLog.set('data_ate', f.data_ate);
        const log = await apiFetch('api/admin/treinamentos/log.php?' + qLog.toString());
        await trnBaixarPdf(trnRelatorioPessoasHtml([pessoa], 'Ficha de treinamentos')
          + `<section class="rel-treinamento"><h2>Log de presença (check-in, check-out e "Estou aqui")</h2>${trnLogHtml(log.eventos || [], true, log.log_disponivel)}</section>`,
          trnNomePdf(nomeArquivo));
      }));
    }catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${esc(e.message)}</p>`;
    }
  }

  function renderTreinamentos(){
    const lista = trnEstado.lista;
    const filtros = trnFiltros();
    document.getElementById('trnContagem').textContent =
      `${lista.length} ${lista.length === 1 ? 'treinamento encontrado' : 'treinamentos encontrados'}`;

    const corpo = document.getElementById('trnCorpo');
    corpo.innerHTML = '';
    if(!lista.length){
      corpo.innerHTML = `<tr><td colspan="10" class="trn-vazio-lista">${
        filtros.modalidade === 'presencial'
          ? 'Treinamentos presenciais ainda não são registrados na Academy — aqui estão só os vídeos (online).'
          : 'Nenhum treinamento com esses filtros.'}</td></tr>`;
      return;
    }

    const vazio = '<span class="trn-vazio">—</span>';
    lista.forEach(t => {
      const perguntasDoFim = t.total_perguntas - t.total_atividades;
      const detalhes = [
        t.trilha ? 'Integração' : (t.area || 'Sem área'),
        t.total_atividades ? `${t.total_atividades} ${t.total_atividades === 1 ? 'atividade' : 'atividades'} no vídeo` : '',
        perguntasDoFim > 0 ? `${perguntasDoFim} ${perguntasDoFim === 1 ? 'pergunta' : 'perguntas'} no fim` : '',
        t.total_perguntas === 0 ? '<span class="aulas-sem-pergunta">sem pergunta</span>' : '',
      ].filter(Boolean);

      const tr = document.createElement('tr');
      if(t.arquivado) tr.className = 'is-arquivado';
      tr.innerHTML = `
        <td class="trn-nowrap">${esc(trnFmtData(t.data))}</td>
        <td class="trn-tema">
          <strong>${esc(t.tema)}</strong>${t.arquivado ? ' <span class="aulas-badge">arquivada</span>' : ''}
          <span class="trn-sub">${detalhes.map(x => x.startsWith('<span') ? x : esc(x)).join(' · ')}</span>
        </td>
        <td><span class="trn-pill trn-pill-online">Online</span></td>
        <td>${esc(t.tipo)}</td>
        <td>${t.normas.length ? esc(t.normas.join(' · ')) : vazio}</td>
        <td>${t.instrutor ? esc(t.instrutor) : vazio}</td>
        <td class="trn-texto">${t.conteudo_programatico
          ? `<span class="trn-clamp" title="${esc(t.conteudo_programatico)}">${esc(t.conteudo_programatico)}</span>`
          : (t.descricao ? `<span class="trn-clamp trn-fallback" title="Ainda sem conteúdo programático — mostrando a descrição do vídeo">${esc(t.descricao)}</span>` : vazio)}</td>
        <td class="trn-texto">${t.assuntos ? `<span class="trn-clamp" title="${esc(t.assuntos)}">${esc(t.assuntos)}</span>` : vazio}</td>
        <td class="trn-nowrap">
          <button type="button" class="trn-link" data-acao="presenca" title="Ver lista de presença">
            ${t.participantes} ${t.participantes === 1 ? 'pessoa' : 'pessoas'}<br><span class="trn-sub">${t.concluidos} ${t.concluidos === 1 ? 'concluiu' : 'concluíram'}</span>
          </button>
        </td>
        <td class="trn-acoes"><div class="trn-acoes-grade">${trnEstado.modo === 'relatorios' ? `
          <button type="button" class="aulas-btn" data-acao="presenca" title="Quem assistiu, com check-in e check-out">Presença</button>
          <button type="button" class="aulas-btn" data-acao="reh" title="Lista de presença REH-002-F1">Lista REH</button>` : `
          <button type="button" class="aulas-btn" data-acao="presenca">Presença</button>
          <button type="button" class="aulas-btn" data-acao="editar">Editar</button>
          <button type="button" class="aulas-btn" data-acao="atividades" title="Perguntas do fim e atividades durante o vídeo">Atividades</button>
          <button type="button" class="aulas-btn" data-acao="areas">Áreas</button>
          <button type="button" class="aulas-btn" data-acao="arquivar">${t.arquivado ? 'Republicar' : 'Arquivar'}</button>
          <button type="button" class="aulas-btn aulas-btn-excluir" data-acao="excluir">Excluir</button>
        `}
        </div></td>
      `;
      tr.querySelectorAll('[data-acao]').forEach(btn => btn.addEventListener('click', () => {
        const acao = btn.dataset.acao;
        if(acao === 'presenca') abrirPresenca(t);
        if(acao === 'reh') trnComBotao(btn, () => baixarRehTreinamento(t, trnFiltros()));
        if(acao === 'editar') abrirEditarTreinamento(t);
        if(acao === 'atividades') abrirPerguntaDaAula(t.id, t.tema);
        if(acao === 'areas') abrirAreasDaAula(t.id, t.tema);
        if(acao === 'arquivar') arquivarAula(t.id, t.arquivado);
        if(acao === 'excluir') excluirAula(t.id);
      }));
      corpo.appendChild(tr);
    });
  }

  // ---------- Lista de presença de um treinamento ----------
  // Tudo preenchido sozinho: dados da pessoa vêm do Portal no login; o
  // resto, do uso do vídeo (abrir, assistir, "Estou aqui", responder).
  function trnTabelaPresencaHtml(participantes, courseId){
    if(!participantes.length){
      return '<p class="trn-vazio-lista">Ninguém participou deste treinamento com esses filtros.</p>';
    }
    const vazio = '<span class="trn-vazio">—</span>';
    return `
      <div class="trn-tabela-wrap">
        <table class="trn-tabela trn-tabela-presenca">
          <thead><tr>
            <th>Participante</th><th>CPF</th><th>Cargo</th><th>Departamento</th>
            <th title="Primeira vez que abriu o vídeo">Check-in</th><th title="Quando concluiu">Check-out</th>
            <th>Assistido</th><th title="Vezes que respondeu &quot;Estou aqui&quot; durante o vídeo">Presença confirmada</th>
            <th title="Perguntas e atividades acertadas">Acertos</th><th>Situação</th>
            <th title="Vezes que entrou no vídeo (check-ins do log)">Sessões</th>
          </tr></thead>
          <tbody>
            ${participantes.map(p => `
              <tr>
                <td><strong>${esc(p.nome)}</strong><span class="trn-sub">${esc(p.email || '')}</span></td>
                <td class="trn-nowrap">${p.cpf ? esc(trnFmtCpf(p.cpf)) : vazio}</td>
                <td>${p.cargo ? esc(p.cargo) : vazio}</td>
                <td>${p.departamento ? esc(p.departamento) : vazio}</td>
                <td class="trn-nowrap">${p.checkin_em ? esc(trnFmtDataHora(p.checkin_em)) : vazio}</td>
                <td class="trn-nowrap">${p.checkout_em ? esc(trnFmtDataHora(p.checkout_em)) : vazio}</td>
                <td class="trn-nowrap">${Math.round(p.watched_pct)}%${p.tempo_assistido_seg != null ? `<span class="trn-sub">≈ ${esc(trnFmtTempo(p.tempo_assistido_seg))}</span>` : ''}</td>
                <td class="trn-nowrap">${p.confirmacoes_presenca}×${p.ultima_confirmacao_em ? `<span class="trn-sub">última ${esc(trnFmtDataHora(p.ultima_confirmacao_em))}</span>` : ''}</td>
                <td class="trn-nowrap">${p.total_perguntas ? `${p.acertos}/${p.total_perguntas}` : vazio}${p.tentativas > p.acertos ? `<span class="trn-sub">${p.tentativas} tentativas</span>` : ''}</td>
                <td><span class="watchers-status ${esc(p.status)}">${esc(trnStatus(p.status))}</span></td>
                <td class="trn-nowrap">${trnSessoesHtml(p, courseId ? p.user_id : null, courseId)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
  }

  async function abrirPresenca(t){
    const body = abrirDialogoAula('Lista de presença', t.tema, true);
    body.innerHTML = `
      <div class="admin-filtros">
        <input type="text" id="presencaBusca" placeholder="Filtrar por nome, e-mail ou CPF...">
        <button type="button" class="trn-btn" id="presencaCsv">Exportar CSV</button>
        <button type="button" class="trn-btn" id="presencaPdf">Baixar PDF</button>
        <button type="button" class="trn-btn" id="presencaReh">Lista REH-002-F1 (PDF)</button>
      </div>
      <p class="admin-field-hint" id="presencaLegenda">
        Check-in: primeira vez que a pessoa abriu o vídeo. Check-out: quando concluiu.
        Assistido: até onde chegou (o vídeo não deixa adiantar). Presença confirmada: vezes que respondeu "Estou aqui".
      </p>
      <div id="presencaTabela"><p>Carregando...</p></div>
    `;
    const f = trnFiltros();
    let participantes = [];
    let pedido = 0;
    const carregar = async () => {
      const meu = ++pedido;
      const q = new URLSearchParams({ course_id: t.id });
      const busca = document.getElementById('presencaBusca').value.trim();
      if(busca) q.set('q', busca);
      if(f.data_de) q.set('data_de', f.data_de);
      if(f.data_ate) q.set('data_ate', f.data_ate);
      try{
        const d = await apiFetch('api/admin/treinamentos/presenca.php?' + q.toString());
        if(meu !== pedido) return;
        participantes = d.participantes || [];
        if(!d.checkin_disponivel){
          document.getElementById('presencaLegenda').insertAdjacentHTML('beforeend',
            ' <strong>O check-in e as confirmações começam a ser gravados depois que a migração 020 rodar no banco.</strong>');
        }
        document.getElementById('presencaTabela').innerHTML = trnTabelaPresencaHtml(participantes, t.id);
      }catch(e){
        document.getElementById('presencaTabela').innerHTML = `<p>Não foi possível carregar: ${esc(e.message)}</p>`;
      }
    };
    ligarBotoesLog(document.getElementById('presencaTabela'));
    let timer = null;
    document.getElementById('presencaBusca').addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(carregar, 300);
    });
    document.getElementById('presencaCsv').addEventListener('click', () => {
      trnBaixarCsv(`presenca-${t.id}-${new Date().toISOString().slice(0, 10)}.csv`,
        trnLinhasCsv([Object.assign({}, t, { lista_presenca: participantes })]));
    });
    const btnPresencaReh = document.getElementById('presencaReh');
    btnPresencaReh.addEventListener('click', () => trnComBotao(btnPresencaReh, () => baixarRehTreinamento(t, f)));
    const btnPresencaPdf = document.getElementById('presencaPdf');
    btnPresencaPdf.addEventListener('click', () => trnComBotao(btnPresencaPdf, () =>
      trnBaixarPdf(trnRelatorioHtml([Object.assign({}, t, { lista_presenca: participantes })], 'Lista de presença'),
        trnNomePdf(`presenca-${trnSlug(t.tema) || t.id}`))));
    carregar();
  }

  // ---------- Editar os dados de um treinamento ----------
  function abrirEditarTreinamento(t){
    const body = abrirDialogoAula('Editar treinamento', t.tema);
    const auditoria = trnEstado.auditoriaAtiva;
    const tipos = trnEstado.tipos.length ? trnEstado.tipos : ['Treinamento interno', 'DDS', 'Capacitação externa', 'Integração', 'Outro'];
    const normas = trnEstado.normas.length ? trnEstado.normas : ['ISO 9001', 'ISO 14001', 'ISO 45001'];
    const desab = auditoria ? '' : 'disabled';
    body.innerHTML = `
      <label class="admin-field-label" for="trnEdTitulo">Tema (título do vídeo)</label>
      <input type="text" id="trnEdTitulo" maxlength="200" value="${esc(t.tema)}">
      <label class="admin-field-label" for="trnEdDescricao">Descrição</label>
      <input type="text" id="trnEdDescricao" value="${esc(t.descricao || '')}">
      ${auditoria ? '' : '<p class="pq-aviso">Os campos abaixo ficam liberados depois que a migração 020 rodar no banco.</p>'}
      <label class="admin-field-label" for="trnEdTipo">Tipo</label>
      <select id="trnEdTipo" ${desab}>
        <option value="">Automático (${t.trilha ? 'Integração' : 'Treinamento interno'})</option>
        ${tipos.map(o => `<option ${t.tipo_preenchido && t.tipo === o ? 'selected' : ''}>${esc(o)}</option>`).join('')}
      </select>
      <span class="admin-field-label">Normas</span>
      <div class="admin-normas" id="trnEdNormas">
        ${normas.map(n => `<label><input type="checkbox" value="${esc(n)}" ${t.normas.includes(n) ? 'checked' : ''} ${desab}> ${esc(n)}</label>`).join('')}
      </div>
      <label class="admin-field-label" for="trnEdInstrutor">Instrutor</label>
      <input type="text" id="trnEdInstrutor" maxlength="150" value="${esc(t.instrutor || '')}" ${desab}>
      <label class="admin-field-label" for="trnEdConteudo">Conteúdo programático</label>
      <textarea id="trnEdConteudo" rows="4" maxlength="5000" ${desab}
        placeholder="${esc(t.descricao ? 'Hoje a busca mostra a descrição: ' + t.descricao : 'Tópicos abordados, na ordem do vídeo')}">${esc(t.conteudo_programatico || '')}</textarea>
      <label class="admin-field-label" for="trnEdAssuntos">Assuntos</label>
      <input type="text" id="trnEdAssuntos" maxlength="500" value="${esc(t.assuntos || '')}" placeholder="Ex.: NR-35, ancoragem, resgate" ${desab}>
      <button type="button" class="admin-modal-submit" id="trnEdSalvar">Salvar</button>
      <div class="admin-modal-feedback" id="trnEdFeedback" hidden></div>
    `;
    const salvar = document.getElementById('trnEdSalvar');
    salvar.addEventListener('click', async () => {
      const fb = document.getElementById('trnEdFeedback');
      const titulo = document.getElementById('trnEdTitulo').value.trim();
      if(!titulo){
        fb.hidden = false; fb.className = 'admin-modal-feedback erro'; fb.textContent = 'O tema não pode ficar vazio.';
        return;
      }
      const corpo = {
        course_id: t.id,
        title: titulo,
        description: document.getElementById('trnEdDescricao').value.trim(),
      };
      if(auditoria){
        Object.assign(corpo, {
          tipo_treinamento: document.getElementById('trnEdTipo').value,
          normas: Array.from(body.querySelectorAll('#trnEdNormas input:checked')).map(i => i.value),
          instrutor: document.getElementById('trnEdInstrutor').value.trim(),
          conteudo_programatico: document.getElementById('trnEdConteudo').value.trim(),
          assuntos: document.getElementById('trnEdAssuntos').value.trim(),
        });
      }
      salvar.disabled = true;
      try{
        await apiFetch('api/admin/courses/update.php', { method: 'POST', body: JSON.stringify(corpo) });
        fb.hidden = false; fb.className = 'admin-modal-feedback ok'; fb.textContent = 'Treinamento atualizado.';
        carregarTreinamentos();
        if(titulo !== t.tema) recarregarTelaConteudo(); // o nome muda na trilha e no catálogo
        setTimeout(closeAulasModal, 900);
      }catch(e){
        fb.hidden = false; fb.className = 'admin-modal-feedback erro'; fb.textContent = e.message;
        salvar.disabled = false;
      }
    });
  }

  // ---------- Exportar ----------
  // CSV com uma linha por participante (e uma linha vazia pro treinamento
  // sem ninguém): é o formato que a auditoria pede e o Excel abre direto.
  function trnLinhasCsv(treinamentos){
    const cab = ['Data de publicação', 'Tema', 'Modalidade', 'Tipo', 'Normas', 'Instrutor', 'Conteúdo programático',
      'Assuntos', 'Participante', 'CPF', 'E-mail', 'Cargo', 'Departamento', 'Check-in', 'Check-out',
      '% assistido', 'Tempo assistido (estimado)', 'Presença confirmada (vezes)', 'Acertos', 'Perguntas', 'Situação',
      'Sessões (check-ins)', 'Última saída (check-out do log)'];
    const linhas = [cab];
    treinamentos.forEach(t => {
      const base = [trnFmtData(t.data), t.tema, 'Online', t.tipo, (t.normas || []).join(' / '), t.instrutor || '',
        t.conteudo_programatico || t.descricao || '', t.assuntos || ''];
      const pessoas = t.lista_presenca || [];
      if(!pessoas.length) linhas.push(base.concat(Array(cab.length - base.length).fill('')));
      pessoas.forEach(p => linhas.push(base.concat([
        p.nome, trnFmtCpf(p.cpf), p.email || '', p.cargo || '', p.departamento || '',
        trnFmtDataHora(p.checkin_em), trnFmtDataHora(p.checkout_em),
        String(Math.round(p.watched_pct)), trnFmtTempo(p.tempo_assistido_seg), String(p.confirmacoes_presenca),
        String(p.acertos), String(p.total_perguntas), trnStatus(p.status),
        String(p.sessoes || 0), trnFmtDataHora(p.ultimo_checkout_em),
      ])));
    });
    return linhas;
  }

  // Ponto e vírgula e BOM: é o que o Excel em português abre com as colunas
  // separadas e os acentos certos.
  function trnBaixarCsv(nome, linhas){
    const texto = linhas.map(l => l.map(c => {
      const v = String(c == null ? '' : c);
      return /[";\n\r]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v;
    }).join(';')).join('\r\n');
    const url = URL.createObjectURL(new Blob(['﻿' + texto], { type: 'text/csv;charset=utf-8' }));
    const a = document.createElement('a');
    a.href = url;
    a.download = nome;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  // Uma linha por pessoa e treinamento; quem não fez nenhum aparece numa
  // linha só, com os campos do treinamento vazios.
  function trnLinhasCsvPessoas(pessoas){
    const cab = ['Participante', 'CPF', 'E-mail', 'Cargo', 'Departamento', 'Treinamento', 'Modalidade', 'Tipo',
      'Normas', 'Instrutor', 'Check-in', 'Check-out', '% assistido', 'Tempo assistido (estimado)',
      'Presença confirmada (vezes)', 'Acertos', 'Perguntas', 'Situação', 'Sessões (check-ins)', 'Última saída (check-out do log)'];
    const linhas = [cab];
    pessoas.forEach(p => {
      const base = [p.nome, trnFmtCpf(p.cpf), p.email || '', p.cargo || '', p.departamento || ''];
      const lista = p.lista_treinamentos || [];
      if(!lista.length) linhas.push(base.concat(['Nenhum treinamento'], Array(cab.length - base.length - 1).fill('')));
      lista.forEach(t => linhas.push(base.concat([
        t.tema, 'Online', t.tipo, (t.normas || []).join(' / '), t.instrutor || '',
        trnFmtDataHora(t.checkin_em), trnFmtDataHora(t.checkout_em), String(Math.round(t.watched_pct)),
        trnFmtTempo(t.tempo_assistido_seg), String(t.confirmacoes_presenca), String(t.acertos),
        String(t.total_perguntas), trnStatus(t.status), String(t.sessoes || 0), trnFmtDataHora(t.ultimo_checkout_em),
      ])));
    });
    return linhas;
  }

  function trnCabecalhoRelatorioHtml(titulo, resumo){
    const f = trnFiltros();
    const filtros = [
      f.q && `Busca: "${f.q}"`, f.tipo && `Tipo: ${f.tipo}`, f.norma && `Norma: ${f.norma}`,
      (f.data_de || f.data_ate) && `Período: ${f.data_de ? trnFmtData(f.data_de) : '…'} a ${f.data_ate ? trnFmtData(f.data_ate) : '…'}`,
    ].filter(Boolean);
    const agora = new Date();
    return `
      <div class="rel-cabecalho">
        <div><strong>MSE Academy</strong> · ${esc(titulo)}</div>
        <div>Gerado em ${agora.toLocaleDateString('pt-BR')} ${agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</div>
      </div>
      <p class="rel-filtros">${filtros.length ? esc(filtros.join(' · ')) : 'Sem filtros'} · ${esc(resumo)}</p>
    `;
  }

  function trnRelatorioPessoasHtml(pessoas, titulo){
    return trnCabecalhoRelatorioHtml(titulo, `${pessoas.length} ${pessoas.length === 1 ? 'pessoa' : 'pessoas'}`)
      + pessoas.map(p => `
        <section class="rel-treinamento">
          <h2>${esc(p.nome)}</h2>
          ${trnPessoaDadosHtml(p)}
          ${trnTabelaFichaHtml(p.lista_treinamentos || [])}
        </section>
      `).join('');
  }

  function trnRelatorioHtml(treinamentos, titulo){
    return trnCabecalhoRelatorioHtml(titulo, `${treinamentos.length} ${treinamentos.length === 1 ? 'treinamento' : 'treinamentos'}`) + `
      ${treinamentos.map(t => `
        <section class="rel-treinamento">
          <h2>${esc(t.tema)}${t.arquivado ? ' (arquivado)' : ''}</h2>
          <dl class="rel-dados">
            <div><dt>Data de publicação</dt><dd>${esc(trnFmtData(t.data))}</dd></div>
            <div><dt>Modalidade</dt><dd>Online</dd></div>
            <div><dt>Tipo</dt><dd>${esc(t.tipo)}</dd></div>
            <div><dt>Normas</dt><dd>${esc((t.normas || []).join(' · ') || '—')}</dd></div>
            <div><dt>Instrutor</dt><dd>${esc(t.instrutor || '—')}</dd></div>
            <div><dt>Assuntos</dt><dd>${esc(t.assuntos || '—')}</dd></div>
            <div class="rel-largo"><dt>Conteúdo programático</dt><dd>${esc(t.conteudo_programatico || t.descricao || '—')}</dd></div>
          </dl>
          ${trnTabelaPresencaHtml(t.lista_presenca || [])}
        </section>
      `).join('')}
    `;
  }

  // ---------- PDF: gera e baixa o arquivo, sem janela de impressão ----------
  // A biblioteca (html2pdf.js, guardada em js/vendor) só é carregada na
  // primeira exportação: são ~900 KB que quem só assiste aula nunca baixa.
  let html2pdfPromise = null;
  function carregarHtml2pdf(){
    if(window.html2pdf) return Promise.resolve(window.html2pdf);
    if(!html2pdfPromise){
      html2pdfPromise = new Promise((resolve, reject) => {
        const tag = document.createElement('script');
        tag.src = 'js/vendor/html2pdf.bundle.min.js';
        tag.onload = () => resolve(window.html2pdf);
        tag.onerror = () => { html2pdfPromise = null; reject(new Error('não consegui carregar o gerador de PDF')); };
        document.head.appendChild(tag);
      });
    }
    return html2pdfPromise;
  }

  function trnSlug(texto){
    return String(texto || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 60);
  }
  function trnNomePdf(base){
    return `${base}-${new Date().toISOString().slice(0, 10)}.pdf`;
  }

  // Relatórios em A4 deitado; o formulário REH-002-F1 em A4 em pé, como
  // o modelo em papel. "html" pode ser uma lista: cada item vira uma folha
  // própria (ex.: um formulário por treinamento) — gerar tudo num bloco só
  // com quebra de página deixava as folhas desalinhadas.
  async function trnBaixarPdf(html, nomeArquivo, opcoes){
    const retrato = !!(opcoes && opcoes.retrato);
    const html2pdf = await carregarHtml2pdf();
    // Largura fixa, um pouco menor que a área útil da folha: sem isso a
    // borda direita das tabelas saía cortada.
    const largura = retrato ? 700 : 1040;
    const montar = (conteudo) => {
      const el = document.createElement('div');
      el.className = 'rel-pdf' + (opcoes && opcoes.classe ? ' ' + opcoes.classe : '');
      el.style.width = largura + 'px';
      el.innerHTML = conteudo;
      return el;
    };
    const folhas = Array.isArray(html) ? html : [html];
    const cssRelatorio = [];
    Array.from(document.styleSheets).forEach(folha => {
      let regras;
      try{ regras = folha.cssRules; }catch(e){ return; } // folha de outro domínio
      Array.from(regras || []).forEach(r => {
        if(r.selectorText && /rel-pdf|rel-formulario/.test(r.selectorText)) cssRelatorio.push(r.cssText);
      });
    });
    let trabalho = html2pdf().set({
      margin: retrato ? 10 : 8,
      filename: nomeArquivo,
      image: { type: 'jpeg', quality: 0.95 },
      // O gerador copia a página e recarrega o style.css; se essa recarga
      // falha, a folha sai sem formatação. As regras do relatório vão
      // direto na cópia, sem depender dela.
      html2canvas: { scale: 2, backgroundColor: '#ffffff', onclone: (doc) => {
        const tag = doc.createElement('style');
        tag.textContent = cssRelatorio.join(' ');
        doc.head.appendChild(tag);
      } },
      jsPDF: { unit: 'mm', format: 'a4', orientation: retrato ? 'portrait' : 'landscape' },
      // Linha de tabela e blocos de dados nunca cortados no meio da página.
      pagebreak: { mode: ['css', 'legacy'], avoid: ['tr', 'h2', '.rel-dados', '.lp-cab', '.lp-dados'] },
    }).from(montar(folhas[0])).toPdf();
    folhas.slice(1).forEach(conteudo => {
      trabalho = trabalho.get('pdf').then(pdf => { pdf.addPage(); })
        .from(montar(conteudo)).toContainer().toCanvas().toPdf();
    });
    await trabalho.save();
  }

  // Botão fica "Gerando PDF..." enquanto o arquivo é montado.
  async function trnComBotao(btn, tarefa){
    const texto = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Gerando PDF...';
    try{
      await tarefa();
    }catch(e){
      alert('Não foi possível gerar o PDF: ' + e.message);
    }finally{
      btn.disabled = false;
      btn.textContent = texto;
    }
  }

  async function trnExportar(formato){
    const btn = document.getElementById(formato === 'csv' ? 'trnExportarCsv' : 'trnExportarPdf');
    const texto = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Gerando...';
    try{
      if(trnEstado.visao === 'pessoas'){
        const d = await apiFetch('api/admin/treinamentos/pessoas.php?' + trnQuery({ com_treinamentos: 1 }));
        const pessoas = d.pessoas || [];
        if(formato === 'csv'){
          trnBaixarCsv(`treinamentos-por-pessoa-${new Date().toISOString().slice(0, 10)}.csv`, trnLinhasCsvPessoas(pessoas));
        } else {
          btn.textContent = 'Gerando PDF...';
          await trnBaixarPdf(trnRelatorioPessoasHtml(pessoas, 'Relatório de treinamentos por pessoa'), trnNomePdf('treinamentos-por-pessoa'));
        }
        return;
      }
      const d = await apiFetch('api/admin/treinamentos/busca.php?' + trnQuery({ com_participantes: 1 }));
      const lista = d.treinamentos || [];
      if(formato === 'csv'){
        trnBaixarCsv(`treinamentos-${new Date().toISOString().slice(0, 10)}.csv`, trnLinhasCsv(lista));
      } else {
        btn.textContent = 'Gerando PDF...';
        await trnBaixarPdf(trnRelatorioHtml(lista, 'Relatório de treinamentos'), trnNomePdf('treinamentos'));
      }
    }catch(e){
      alert('Não foi possível exportar: ' + e.message);
    }finally{
      btn.disabled = false;
      btn.textContent = texto;
    }
  }

  function ligarTelaTreinamentos(){
    const tela = document.getElementById('treinamentosTela');
    if(!tela) return;
    document.getElementById('trnFechar').addEventListener('click', closeTreinamentosTela);

    const recarregarEmBreve = () => {
      clearTimeout(trnEstado.timer);
      trnEstado.timer = setTimeout(carregarTreinamentos, 300);
    };
    document.getElementById('trnBusca').addEventListener('input', recarregarEmBreve);
    ['trnTipo', 'trnNorma', 'trnDataDe', 'trnDataAte'].forEach(id =>
      document.getElementById(id).addEventListener('change', carregarTreinamentos));
    document.querySelectorAll('#trnModalidade button').forEach(b => b.addEventListener('click', () => {
      document.querySelectorAll('#trnModalidade button').forEach(x => x.setAttribute('aria-pressed', String(x === b)));
      carregarTreinamentos();
    }));
    document.getElementById('trnLimpar').addEventListener('click', () => {
      ['trnBusca', 'trnTipo', 'trnNorma', 'trnDataDe', 'trnDataAte'].forEach(id => { document.getElementById(id).value = ''; });
      document.querySelectorAll('#trnModalidade button').forEach((x, i) => x.setAttribute('aria-pressed', String(i === 0)));
      carregarTreinamentos();
    });
    // Por treinamento / Por pessoa: os mesmos filtros valem pras duas.
    document.querySelectorAll('#trnVisao button').forEach(b => b.addEventListener('click', () => {
      trnMudarVisao(b.dataset.visao);
      carregarTreinamentos();
    }));
    document.getElementById('trnAtualizarPortal').addEventListener('click', atualizarPeloPortal);
    document.getElementById('trnExportarCsv').addEventListener('click', () => trnExportar('csv'));
    document.getElementById('trnExportarPdf').addEventListener('click', () => trnExportar('pdf'));
    document.getElementById('trnNovoVideo').addEventListener('click', openVideoModal);

    // Esc fecha a janela de ação primeiro; sem nenhuma aberta, fecha a tela.
    document.addEventListener('keydown', (e) => {
      if(e.key !== 'Escape' || tela.hidden) return;
      if(!document.getElementById('aulasModalOverlay').hidden){ closeAulasModal(); return; }
      if(!document.getElementById('videoModalOverlay').hidden) return; // o modal de vídeo cuida do Esc dele
      closeTreinamentosTela();
    });
  }

  // ---------- Pergunta de uma aula já cadastrada ----------
  // Antes a pergunta só podia ser escrita ao cadastrar o vídeo. Quem
  // subisse a aula sem pergunta não tinha caminho nenhum: teria que apagar
  // a aula e subir o vídeo de novo, perdendo o registro de quem assistiu.
  //
  // Uma pergunta por aula, que é como o resto do sistema trata: o cadastro
  // grava uma, a tela mostra uma, a conta do baú é de uma por módulo.
  // ---------- Perguntas de uma aula já cadastrada ----------
  // Antes a pergunta só podia ser escrita ao cadastrar o vídeo, e só uma.
  // Quem subisse a aula sem pergunta — ou quisesse uma segunda — teria que
  // apagar a aula e subir o vídeo de novo, perdendo o registro de quem já
  // assistiu.
  //
  // O painel edita o conjunto inteiro e manda tudo de uma vez. Adicionar e
  // tirar mexem só no bloco em questão, sem redesenhar os outros: redesenhar
  // perderia o que já estava digitado nos demais.
  const QUIZ_MAX_PERGUNTAS = 10;

  //
  // Cada pergunta pode aparecer no fim do vídeo (como sempre foi) ou
  // DURANTE o vídeo, no minuto e segundo escolhidos: o vídeo pausa ali e só
  // continua depois da resposta certa. Vale também pra vídeo que já está no
  // ar — e marcar ou mudar o minuto não apaga as respostas já dadas.
  function segundosParaMmss(seg){
    return String(Math.floor(seg / 60)).padStart(2, '0') + ':' + String(seg % 60).padStart(2, '0');
  }

  async function abrirPerguntaDaAula(courseId, titulo){
    const painel = abrirDialogoAula('Perguntas e atividades', titulo);
    painel.innerHTML = '<p>Carregando...</p>';

    try{
      const d = await apiFetch('api/admin/courses/quiz.php?course_id=' + encodeURIComponent(courseId));
      const maxPerguntas = d.max_perguntas || QUIZ_MAX_PERGUNTAS;

      const duracao = d.duracao_seg || 0;
      painel.innerHTML = `
        <div class="aulas-pergunta-form">
          <p class="admin-field-hint">
            <strong>Durante o vídeo:</strong> o vídeo pausa no minuto e segundo escolhidos e só continua depois da resposta certa.
            <strong>No fim do vídeo:</strong> aparece quando o vídeo termina.
            ${duracao ? `Duração do vídeo: <strong>${segundosParaMmss(duracao)}</strong>.` : 'A duração do vídeo aparece aqui depois que alguém assistir pela primeira vez.'}
          </p>
          ${d.aceita_momento === false ? '<p class="pq-aviso">Perguntas durante o vídeo ficam disponíveis depois que a migração 020 rodar no banco.</p>' : ''}
          <div class="pq-lista"></div>
          <button type="button" class="pq-add-pergunta">+ Adicionar pergunta</button>
          <div class="pq-acoes">
            <button type="button" class="admin-modal-submit pq-salvar">Salvar</button>
            <button type="button" class="pq-fechar">Fechar</button>
          </div>
          ${d.respostas > 0 ? `<p class="pq-aviso">${d.respostas} ${d.respostas === 1 ? 'resposta já foi registrada' : 'respostas já foram registradas'} nesta aula.</p>` : ''}
          <div class="admin-modal-feedback pq-feedback" hidden></div>
        </div>
      `;

      const lista = painel.querySelector('.pq-lista');
      const btnAddPergunta = painel.querySelector('.pq-add-pergunta');
      const aviso = painel.querySelector('.pq-feedback');
      const mostrar = (texto, erro) => {
        aviso.hidden = false;
        aviso.textContent = texto;
        aviso.classList.toggle('erro', !!erro);
      };

      const blocos = [];
      let proximoId = 0;

      // A numeração é refeita a cada mudança: com "Pergunta 1, 2, 3" fixo no
      // momento da criação, tirar a do meio deixaria "1, 3".
      function renumerar(){
        blocos.forEach((b, i) => {
          b.el.querySelector('.pq-num').textContent = 'Pergunta ' + (i + 1);
          // Com uma só, tirar deixaria a aula sem pergunta nenhuma — pra
          // isso existe o "Tirar todas", que avisa antes.
          b.el.querySelector('.pq-tirar').hidden = blocos.length < 2;
        });
        btnAddPergunta.hidden = blocos.length >= maxPerguntas;
        painel.querySelector('.pq-vazio')?.remove();
        if(blocos.length === 0){
          lista.insertAdjacentHTML('beforeend',
            '<p class="pq-vazio">Esta aula está sem pergunta. Clique abaixo pra criar a primeira.</p>');
        }
      }

      function adicionarBloco(dados){
        const uid = proximoId++;
        const el = document.createElement('div');
        el.className = 'pq-bloco';
        el.innerHTML = `
          <div class="pq-bloco-topo">
            <span class="pq-num"></span>
            <button type="button" class="pq-tirar" title="Tirar esta pergunta">Tirar</button>
          </div>
          <input type="text" class="pq-texto" maxlength="500" value="${esc(dados && dados.question || '')}"
                 placeholder="Ex: Qual EPI é obrigatório na obra?">
          <div class="pq-momento">
            <label class="pq-momento-rotulo" for="pq-quando-${courseId}-${uid}">Quando aparece</label>
            <select class="pq-quando" id="pq-quando-${courseId}-${uid}" ${d.aceita_momento === false ? 'disabled' : ''}>
              <option value="fim">No fim do vídeo</option>
              <option value="durante">Durante o vídeo, em</option>
            </select>
            <span class="pq-tempo" hidden>
              <input type="number" class="pq-min" min="0" max="999" inputmode="numeric" aria-label="Minuto"> min
              <input type="number" class="pq-seg" min="0" max="59" inputmode="numeric" aria-label="Segundo"> s
            </span>
          </div>
          <div class="pq-opcoes"></div>
        `;
        lista.appendChild(el);

        const quando = el.querySelector('.pq-quando');
        const tempo = el.querySelector('.pq-tempo');
        const momento = dados && dados.momento_seg;
        if(momento != null){
          quando.value = 'durante';
          el.querySelector('.pq-min').value = Math.floor(momento / 60);
          el.querySelector('.pq-seg').value = momento % 60;
        }
        tempo.hidden = quando.value !== 'durante';
        quando.addEventListener('change', () => {
          tempo.hidden = quando.value !== 'durante';
          if(!tempo.hidden) el.querySelector('.pq-min').focus();
        });

        const editor = criarEditorDeOpcoes(
          el.querySelector('.pq-opcoes'),
          'pq-certa-' + courseId + '-' + uid,
          dados && dados.options
        );

        el.querySelector('.pq-tirar').addEventListener('click', () => {
          const i = blocos.findIndex(b => b.el === el);
          if(i >= 0) blocos.splice(i, 1);
          el.remove();
          renumerar();
        });

        blocos.push({ el, editor });
        renumerar();
        return el;
      }

      (d.questions || []).forEach(q => adicionarBloco(q));
      if(!(d.questions || []).length) adicionarBloco(null);

      btnAddPergunta.addEventListener('click', () => {
        const el = adicionarBloco(null);
        el.querySelector('.pq-texto').focus();
      });

      // Momento em segundos, ou null pra "no fim". Erro de digitação volta
      // como texto, antes de mandar: o servidor confere de novo.
      const lerMomento = (b, i) => {
        if(b.el.querySelector('.pq-quando').value !== 'durante') return null;
        const min = parseInt(b.el.querySelector('.pq-min').value || '0', 10);
        const seg = parseInt(b.el.querySelector('.pq-seg').value || '0', 10);
        if(isNaN(min) || isNaN(seg) || min < 0 || seg < 0 || seg > 59){
          throw new Error(`Pergunta ${i + 1}: o segundo vai de 0 a 59.`);
        }
        const total = min * 60 + seg;
        if(total < 1) throw new Error(`Pergunta ${i + 1}: escolha o minuto e o segundo em que ela aparece (a partir de 00:01).`);
        if(duracao && total >= duracao){
          throw new Error(`Pergunta ${i + 1}: ${segundosParaMmss(total)} passa do fim do vídeo (${segundosParaMmss(duracao)}).`);
        }
        return total;
      };

      const montarCorpo = () => ({
        course_id: courseId,
        questions: blocos.map((b, i) => ({
          question: b.el.querySelector('.pq-texto').value.trim(),
          momento_seg: lerMomento(b, i),
          options: b.editor.ler(),
        })),
      });

      // Mandar duas vezes: a primeira pode voltar pedindo confirmação,
      // quando já houve respostas — regravar apaga o registro delas, e isso
      // não pode acontecer num clique só.
      const enviar = async (corpo, botao) => {
        botao.disabled = true;
        try{
          let r = await apiFetch('api/admin/courses/quiz.php', {
            method: 'POST', body: JSON.stringify(corpo)
          });
          if(r.precisa_confirmar){
            if(!confirm(r.message + '\n\nContinuar?')){ botao.disabled = false; return; }
            r = await apiFetch('api/admin/courses/quiz.php', {
              method: 'POST', body: JSON.stringify({ ...corpo, confirmar: true })
            });
          }
          mostrar(r.message, false);
          botao.disabled = false;
          detalheCache.delete(courseId); // senão a aula volta do cache sem as perguntas novas
          await recarregarTelaConteudo();
          carregarTreinamentos();
        }catch(e){
          mostrar(e.message, true);
          botao.disabled = false;
        }
      };

      const btnSalvar = painel.querySelector('.pq-salvar');
      btnSalvar.addEventListener('click', () => {
        let corpo;
        try{ corpo = montarCorpo(); }catch(e){ mostrar(e.message, true); return; }
        enviar(corpo, btnSalvar);
      });

      painel.querySelector('.pq-fechar').addEventListener('click', closeAulasModal);
    }catch(e){
      painel.innerHTML = `<p class="aulas-pergunta-form">Não foi possível carregar as perguntas: ${esc(e.message)}</p>`;
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
    const body = abrirDialogoAula('Áreas obrigatórias', titulo);
    body.innerHTML = '<p>Carregando áreas...</p>';

    let dados;
    try {
      dados = await apiFetch('api/admin/courses/areas.php?course_id=' + encodeURIComponent(courseId));
    } catch(e){
      body.innerHTML = `<p>Não foi possível carregar: ${esc(e.message)}</p>`;
      return;
    }

    body.innerHTML = `
      <p class="admin-field-hint">Marque as áreas para as quais esta aula é <strong>obrigatória</strong>.
        Sem nenhuma marcada, vale para todos. Quem não é da área marcada continua vendo a aula —
        ela só não entra nas pendências nem na barra de progresso dessa pessoa.</p>
      <div class="areas-aula-lista">
        ${dados.areas.map(a => `
          <label class="areas-aula-item">
            <input type="checkbox" value="${a.id}" ${a.marcada ? 'checked' : ''}>
            <span>${esc(a.name)}</span>
          </label>
        `).join('')}
      </div>
      <button type="button" class="admin-modal-submit" id="areasSalvar">Salvar</button>
      <div class="admin-modal-feedback" id="areasFeedback" hidden></div>
    `;

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
        carregarTreinamentos();
        setTimeout(closeAulasModal, 1200);
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
      carregarTreinamentos();
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
      carregarTreinamentos();
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
      carregarTreinamentos();
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
    carregarTreinamentosLP();
  }

  // ---------- Lista de Presença (formulário REH-002-F1) ----------
  // O formulário oficial de lista de presença, preenchido pelo sistema:
  // nada é digitado. Os dados do treinamento vêm do cadastro da aula
  // (Treinamentos > Editar) e os participantes, do check-in/check-out
  // gravado pelo player.
  const LP_FORMULARIO = { codigo: 'REH-002-F1', revisao: '04', emissao: '10/2026' };
  const LP_LINHAS_PRIMEIRA_PAGINA = 26; // como no formulário em papel
  const LP_LINHAS_POR_PAGINA = 38;
  const lpEstado = { treinamentos: [] };

  async function carregarTreinamentosLP(){
    const sel = document.getElementById('lpTreinamento');
    if(!sel) return;
    try{
      const d = await apiFetch('api/admin/treinamentos/busca.php');
      lpEstado.treinamentos = (d.treinamentos || []).slice().sort((a, b) => a.tema.localeCompare(b.tema, 'pt-BR'));
      const atual = sel.value;
      sel.innerHTML = '<option value="">Escolha o treinamento...</option>' + lpEstado.treinamentos.map(t =>
        `<option value="${t.id}">${esc(t.tema)}${t.arquivado ? ' (arquivada)' : ''} — ${t.participantes} ${t.participantes === 1 ? 'participante' : 'participantes'}</option>`
      ).join('');
      sel.value = atual;
    }catch(e){
      sel.innerHTML = '<option value="">Não foi possível carregar os treinamentos</option>';
    }
    // Pessoas, pro formulário por pessoa.
    const selPessoa = document.getElementById('lpPessoa');
    try{
      const d = await apiFetch('api/admin/treinamentos/pessoas.php');
      const atual = selPessoa.value;
      selPessoa.innerHTML = '<option value="">Escolha a pessoa...</option>' + (d.pessoas || []).map(p =>
        `<option value="${p.user_id}">${esc(p.nome)} — ${p.treinamentos} ${p.treinamentos === 1 ? 'treinamento' : 'treinamentos'}</option>`
      ).join('');
      selPessoa.value = atual;
    }catch(e){
      selPessoa.innerHTML = '<option value="">Não foi possível carregar as pessoas</option>';
    }
  }

  // Motivo não existe no cadastro: sai do tipo do treinamento e das áreas
  // pra quais a aula é obrigatória.
  function lpMotivo(t){
    const areas = (t.areas_obrigatorias || []).join(', ');
    if(t.tipo === 'DDS') return 'Diálogo Diário de Segurança (DDS)';
    if(t.tipo === 'Integração' || t.trilha) return 'Integração de novos colaboradores' + (areas ? ` — obrigatório para ${areas}` : '');
    if(t.tipo === 'Capacitação externa') return 'Capacitação externa' + (areas ? ` — ${areas}` : '');
    return 'Capacitação e atualização profissional' + (areas ? ` — obrigatório para ${areas}` : (t.area ? ` — ${t.area}` : ''));
  }

  function lpCargaHoraria(t){
    const seg = t.duracao_seg || (t.duracao_min ? t.duracao_min * 60 : 0);
    if(!seg) return '';
    const h = Math.floor(seg / 3600), m = Math.round((seg % 3600) / 60);
    return h ? `${h}h${m ? String(m).padStart(2, '0') + 'min' : ''}` : `${Math.max(m, 1)}min`;
  }

  function lpCaixa(marcado, rotulo){
    return `<span class="lp-caixa${marcado ? ' is-marcado' : ''}"></span> ${esc(rotulo)}`;
  }

  // Setor do treinamento: áreas pra quais é obrigatório, a área do vídeo
  // ou, na integração, todos.
  function lpSetorDoTreinamento(t){
    return t.trilha ? 'Todos os setores (integração)' : ((t.areas_obrigatorias || []).join(', ') || t.area || 'Todos os setores');
  }

  // Formulário. Por pessoa (opcoes.porPessoa), cada linha é um treinamento
  // e a tabela ganha a coluna TREINAMENTO; t traz os campos já somados.
  function lpFormularioHtml(t, participantes, paginaTexto, opcoes){
    const porPessoa = !!(opcoes && opcoes.porPessoa);
    // Data e horários: do primeiro check-in ao último check-out. Num
    // treinamento online cada pessoa assiste num dia — se foram vários,
    // a data vira um intervalo e o horário leva a data junto.
    const inicios = participantes.map(p => p.checkin_em || p.checkout_em).filter(Boolean).sort();
    // Check-out: a conclusão; sem conclusão, a última saída do log.
    const saida = (p) => p.checkout_em || p.ultimo_checkout_em || '';
    const fins = participantes.map(saida).filter(Boolean).sort();
    const primeiro = inicios[0] || '', ultimo = fins[fins.length - 1] || '';
    const dias = [...new Set(inicios.concat(fins).map(d => d.slice(0, 10)))].sort();
    const umDia = dias.length <= 1;
    const data = !dias.length ? '' : umDia ? trnFmtData(dias[0]) : `${trnFmtData(dias[0])} a ${trnFmtData(dias[dias.length - 1])}`;
    const hora = (s) => !s ? '' : umDia ? s.slice(11, 16) : trnFmtDataHora(s);
    const normas = t.normas || [];
    const setor = t.setorTexto || lpSetorDoTreinamento(t);

    // SETOR da linha: o departamento da pessoa; sem departamento no
    // cadastro, o setor do treinamento — a coluna não fica em branco.
    const linhas = participantes.map(p => `
      <tr>
        <td>${esc(p.departamento || p.setorTreinamento || setor)}</td>
        <td>${esc(p.nome)}</td>
        ${porPessoa ? `<td>${esc(p.treinamento || '')}</td>` : ''}
        <td>${esc(p.email || '')}</td>
        <td>${esc(trnFmtDataHora(p.checkin_em))}</td>
        <td>${esc(trnFmtDataHora(saida(p)))}</td>
      </tr>`);
    const vazia = '<tr>' + '<td></td>'.repeat(porPessoa ? 6 : 5) + '</tr>';
    while(linhas.length < LP_LINHAS_PRIMEIRA_PAGINA) linhas.push(vazia);
    const tipos = t.tipos || [t.tipo];
    const paginas = participantes.length <= LP_LINHAS_PRIMEIRA_PAGINA ? 1
      : 1 + Math.ceil((participantes.length - LP_LINHAS_PRIMEIRA_PAGINA) / LP_LINHAS_POR_PAGINA);

    return `
      <table class="lp-cab">
        <tr>
          <td rowspan="4" class="lp-logo"><div class="lp-logo-mse">mse</div><div class="lp-logo-sub">lm\\spagnuolo</div></td>
          <td class="lp-titulo-a">FORMULÁRIO</td>
          <td class="lp-meta">Código: ${LP_FORMULARIO.codigo}</td>
        </tr>
        <tr><td rowspan="3" class="lp-titulo-b">LISTA DE PRESENÇA</td><td class="lp-meta">Revisão: ${LP_FORMULARIO.revisao}</td></tr>
        <tr><td class="lp-meta">Emissão: ${LP_FORMULARIO.emissao}</td></tr>
        <tr><td class="lp-meta">Páginas: ${paginaTexto || paginas}</td></tr>
      </table>
      <p class="lp-atencao">ATENÇÃO: Presença registrada exclusivamente online, por check-in e check-out (identificação do participante, data e hora).</p>
      <table class="lp-dados">
        <colgroup><col><col><col><col><col><col></colgroup>
        <tr>
          <td colspan="2">${lpCaixa(tipos.includes('Treinamento interno') || tipos.includes('Integração'), 'TREINAMENTO INTERNO')}</td>
          <td colspan="2">${lpCaixa(tipos.includes('DDS'), 'DDS')}</td>
          <td colspan="2">${lpCaixa(tipos.includes('Capacitação externa'), 'CAPACITAÇÃO EXTERNA')}</td>
        </tr>
        <tr><td colspan="6"><b>TEMA:</b> ${esc(t.tema)}</td></tr>
        <tr><td colspan="6"><b>MOTIVO:</b> ${esc(t.motivoTexto || lpMotivo(t))}</td></tr>
        <tr><td colspan="6" class="lp-conteudo"><b>CONTEÚDO PROGRAMÁTICO:</b> ${esc(t.conteudo_programatico || t.descricao || '')}</td></tr>
        <tr>
          <td colspan="5"><b>NORMA:</b> ${esc(normas.join(' · ') || '-')}</td>
          <td>${lpCaixa(!normas.length, 'Não se aplica')}</td>
        </tr>
        <tr><td colspan="6"><b>INSTRUTOR:</b> ${esc(t.instrutor || '')}</td></tr>
        <tr><td colspan="6"><b>SETOR:</b> ${esc(setor)}</td></tr>
        <tr>
          <td colspan="2"><b>DATA:</b> ${esc(data)}</td>
          <td colspan="2"><b>HORA INICIAL:</b> ${esc(hora(primeiro))}</td>
          <td colspan="2"><b>HORA FINAL:</b> ${esc(hora(ultimo))}</td>
        </tr>
        <tr><td colspan="6"><b>CARGA HORÁRIA:</b> ${esc(lpCargaHoraria(t))}${porPessoa ? ' (soma dos treinamentos)' : ''}</td></tr>
      </table>
      <table class="lp-participantes${porPessoa ? ' lp-por-pessoa' : ''}">
        <thead><tr><th>SETOR</th><th>NOME DO PARTICIPANTE</th>${porPessoa ? '<th>TREINAMENTO</th>' : ''}<th>DOCUMENTO</th><th>CHECK-IN</th><th>CHECK-OUT</th></tr></thead>
        <tbody>${linhas.join('')}</tbody>
      </table>
      <p class="lp-rodape">Registro eletrônico de check-in/check-out (data e hora) substitui a assinatura e é retido como informação documentada.</p>
    `;
  }

  // Dados dos treinamentos (setor, áreas, carga horária) que o formulário
  // usa; vêm da busca e ficam guardados.
  async function garantirTreinamentosLP(){
    if(!lpEstado.treinamentos.length){
      const d = await apiFetch('api/admin/treinamentos/busca.php');
      lpEstado.treinamentos = d.treinamentos || [];
    }
    return lpEstado.treinamentos;
  }

  function lpPeriodo(filtros){
    const q = new URLSearchParams();
    if(filtros && filtros.data_de) q.set('data_de', filtros.data_de);
    if(filtros && filtros.data_ate) q.set('data_ate', filtros.data_ate);
    return q;
  }

  // REH-002-F1 por pessoa: um formulário só, numa página, com uma linha
  // por treinamento que a pessoa fez. O cabeçalho junta os treinamentos:
  // temas, tipos marcados, normas, instrutores, período e carga somada.
  async function baixarRehPessoa(userId, filtros){
    const q = lpPeriodo(filtros);
    q.set('user_id', userId);
    const [d] = await Promise.all([apiFetch('api/admin/treinamentos/pessoas.php?' + q.toString()), garantirTreinamentosLP()]);
    const pessoa = d.pessoa;
    const lista = d.treinamentos || [];
    if(!lista.length){
      throw new Error(`${pessoa.nome} não tem treinamento${filtros && (filtros.data_de || filtros.data_ate) ? ' nesse período' : ''}.`);
    }
    const metas = lista.map(t => Object.assign({}, t, lpEstado.treinamentos.find(x => x.id === t.id) || {}));
    const unicos = (valores) => [...new Set(valores.filter(Boolean))];
    const segundos = metas.reduce((soma, m) => soma + (m.duracao_seg || (m.duracao_min ? m.duracao_min * 60 : 0)), 0);
    const resumo = {
      tema: metas.map(m => m.tema).join('; '),
      tipos: unicos(metas.map(m => m.tipo)),
      motivoTexto: unicos(metas.map(lpMotivo)).join('; '),
      conteudo_programatico: metas.map(m => `${m.tema}: ${m.conteudo_programatico || m.descricao || '-'}`).join(' | '),
      normas: unicos([].concat(...metas.map(m => m.normas || []))),
      instrutor: unicos(metas.map(m => m.instrutor)).join(', '),
      setorTexto: pessoa.departamento || unicos(metas.map(lpSetorDoTreinamento)).join(', '),
      duracao_seg: segundos,
    };
    const linhas = metas.map(m => ({
      departamento: pessoa.departamento, setorTreinamento: lpSetorDoTreinamento(m),
      nome: pessoa.nome, email: pessoa.email, treinamento: m.tema,
      checkin_em: m.checkin_em, checkout_em: m.checkout_em, ultimo_checkout_em: m.ultimo_checkout_em,
    }));
    await trnBaixarPdf(lpFormularioHtml(resumo, linhas, null, { porPessoa: true }),
      trnNomePdf(`lista-presenca-REH-002-F1-${trnSlug(pessoa.nome) || pessoa.user_id}`), { retrato: true, classe: 'rel-formulario' });
    return lista.length;
  }

  // REH-002-F1 de um treinamento, com todos os participantes.
  async function baixarRehTreinamento(t, filtros){
    const q = lpPeriodo(filtros);
    q.set('course_id', t.id);
    const [d] = await Promise.all([apiFetch('api/admin/treinamentos/presenca.php?' + q.toString()), garantirTreinamentosLP()]);
    const meta = Object.assign({}, lpEstado.treinamentos.find(x => x.id === t.id) || {}, t);
    const participantes = d.participantes || [];
    await trnBaixarPdf(lpFormularioHtml(meta, participantes),
      trnNomePdf(`lista-presenca-REH-002-F1-${trnSlug(meta.tema) || meta.id}`), { retrato: true, classe: 'rel-formulario' });
    return participantes.length;
  }

  async function gerarListaPresencaPessoa(){
    const fb = document.getElementById('lpFeedback');
    const userId = document.getElementById('lpPessoa').value;
    if(!userId){
      fb.hidden = false; fb.className = 'admin-modal-feedback erro'; fb.textContent = 'Escolha a pessoa.';
      return;
    }
    await lpGerarNoPainel(() => baixarRehPessoa(userId, {
      data_de: document.getElementById('lpDataDe').value, data_ate: document.getElementById('lpDataAte').value,
    }), 'treinamentos');
  }

  async function gerarListaPresencaFormulario(){
    if(document.querySelector('#lpModo [data-modo="pessoa"]').getAttribute('aria-pressed') === 'true'){
      return gerarListaPresencaPessoa();
    }
    const fb = document.getElementById('lpFeedback');
    const t = lpEstado.treinamentos.find(x => String(x.id) === document.getElementById('lpTreinamento').value);
    if(!t){
      fb.hidden = false; fb.className = 'admin-modal-feedback erro'; fb.textContent = 'Escolha o treinamento.';
      return;
    }
    await lpGerarNoPainel(() => baixarRehTreinamento(t, {
      data_de: document.getElementById('lpDataDe').value, data_ate: document.getElementById('lpDataAte').value,
    }), 'participantes');
  }

  async function lpGerarNoPainel(gerar, rotulo){
    const fb = document.getElementById('lpFeedback');
    const btn = document.getElementById('lpGerar');
    btn.disabled = true;
    btn.textContent = 'Gerando PDF...';
    try{
      const n = await gerar();
      fb.hidden = false; fb.className = 'admin-modal-feedback ok'; fb.textContent = `PDF baixado (${n} ${rotulo}).`;
    }catch(e){
      fb.hidden = false; fb.className = 'admin-modal-feedback erro'; fb.textContent = 'Não foi possível gerar: ' + e.message;
    }finally{
      btn.disabled = false;
      btn.textContent = 'Gerar lista de presença (PDF)';
    }
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
      ['btnAdicionarPessoas', 'btnAdicionarVideo', 'btnGerenciarAulas', 'btnRelatorios', 'btnDepartamentos'].forEach(id => {
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
    if(btnAulas) btnAulas.addEventListener('click', () => openTreinamentosTela('gestao'));
    // Relatórios: Acessos, Quem assistiu e Relatório por pessoa numa tela só.
    const btnRelatorios = document.getElementById('btnRelatorios');
    if(btnRelatorios) btnRelatorios.addEventListener('click', () => openTreinamentosTela('relatorios'));
    ligarTelaTreinamentos();
    const btnAulasClose = document.getElementById('aulasModalClose');
    if(btnAulasClose) btnAulasClose.addEventListener('click', closeAulasModal);

    const btnAreas = document.getElementById('btnDepartamentos');
    if(btnAreas) btnAreas.addEventListener('click', openAreasModal);
    const btnAreasClose = document.getElementById('areasModalClose');
    if(btnAreasClose) btnAreasClose.addEventListener('click', closeAreasModal);
    const aulasOverlay = document.getElementById('aulasModalOverlay');
    if(aulasOverlay) aulasOverlay.addEventListener('click', (e) => {
      if(e.target === aulasOverlay) closeAulasModal();
    });

    const btnLp = document.getElementById('lpGerar');
    if(btnLp) btnLp.addEventListener('click', gerarListaPresencaFormulario);
    // Formulário por treinamento ou por pessoa.
    document.querySelectorAll('#lpModo button').forEach(b => b.addEventListener('click', () => {
      document.querySelectorAll('#lpModo button').forEach(x => x.setAttribute('aria-pressed', String(x === b)));
      const porPessoa = b.dataset.modo === 'pessoa';
      document.getElementById('lpTreinamentoWrap').hidden = porPessoa;
      document.getElementById('lpPessoaWrap').hidden = !porPessoa;
      document.getElementById('lpFeedback').hidden = true;
    }));
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
