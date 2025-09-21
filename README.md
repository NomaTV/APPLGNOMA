# APPLGNOMA[NOMA_WEBOS_COMPLETO.md](https://github.com/user-attachments/files/22451464/NOMA_WEBOS_COMPLETO.md)
# 📺 NomaTV - Documentação Técnica Completa v2.0

**Versão:** 2.0 Final | **Data:** 14/09/2025 | **Foco:** Sistema Completo Smart TV + Backend

## 📋 Índice Geral

### 📱 SEÇÃO 1: APP SMART TV
1. [🚀 Index Casca + Server.js](#-index-casca--serverjs)
2. [🔐 Autenticando.html](#-autenticandohtml)
3. [📄 Index Corpo](#-index-corpo)
4. [🏠 Home.html](#-homehtml)
5. [📺 Canais.html](#-canaishtml)
6. [🎬 Filmes.html](#-filmeshtml)
7. [📚 Séries.html](#-serieshtml)
8. [⚙️ Funções Globais (22 APIs)](#️-funções-globais-22-apis)
9. [🎨 CSS Otimizado Smart TV](#-css-otimizado-smart-tv)
10. [🛠️ Templates ES5](#️-templates-es5)
11. [⚡ Performance TV](#-performance-tv)

### 🖥️ SEÇÃO 2: FRONTEND PAINÉIS WEB
12. [👑 admin.html (master='admin')](#-adminhtml-masteradmin)
13. [🏪 revendedor.html (master='sim')](#-revendedorhtml-mastersim)
14. [👤 sub_revendedor.html (master='nao')](#-sub_revendedorhtml-masternao)
15. [🌐 api.js - Ponte Frontend-Backend](#-apijs---

## 📜 **Termos.html**

### **📁 Arquivo:**
- `termos.html` (Tela de aceite dos termos de uso)

### **🎯 Responsabilidades:**
- Exibir termos de uso e política de privacidade
- Capturar aceite/recusa do usuário
- Persistir decisão no localStorage
- Navegação condicional (aceita → index-corpo, recusa → decline)
- Interface otimizada para controle remoto

### **🔄 Fluxo de Inicialização:**
```javascript
function inicializarTermos() {
    console.log('[TERMOS] Inicializando tela de termos...');
    
    // Verificar se já foi aceito (não deveria chegar aqui)
    var aceitouTermos = localStorage.getItem('aceitouTermos');
    if (aceitouTermos === 'true') {
        console.log('[TERMOS] Termos já aceitos - redirecionando');
        window.nav('index');
        return;
    }
    
    // Resolver elementos DOM
    var btnAceitar = document.getElementById('btnAceitar');
    var btnRecusar = document.getElementById('btnRecusar');
    var termosTexto = document.getElementById('termosTexto');
    
    if (!btnAceitar || !btnRecusar || !termosTexto) {
        console.error('[TERMOS] Elementos DOM não encontrados');
        return;
    }
    
    // Aplicar foco inicial
    btnAceitar.focus();
    btnAceitar.classList.add('lg-focused');
    
    // Setup navegação por controle
    setupTermosNavigation();
    
    // Carregar termos dinâmicos
    carregarTermosConteudo();
    
    // Setup handlers
    btnAceitar.addEventListener('click', aceitarTermos);
    btnRecusar.addEventListener('click', recusarTermos);
    
    // Keyboard navigation
    document.addEventListener('keydown', handleTermosKeyboard);
}

function setupTermosNavigation() {
    var botoes = document.querySelectorAll('.termo-btn');
    var currentFocus = 0;
    
    function updateFocus() {
        // Remove foco de todos
        for (var i = 0; i < botoes.length; i++) {
            botoes[i].classList.remove('lg-focused');
        }
        // Aplica foco no atual
        if (botoes[currentFocus]) {
            botoes[currentFocus].classList.add('lg-focused');
            botoes[currentFocus].focus();
        }
    }
    
    window.termosNavigation = {
        next: function() {
            currentFocus = (currentFocus + 1) % botoes.length;
            updateFocus();
        },
        prev: function() {
            currentFocus = (currentFocus - 1 + botoes.length) % botoes.length;
            updateFocus();
        },
        select: function() {
            if (botoes[currentFocus]) {
                botoes[currentFocus].click();
            }
        }
    };
}

function handleTermosKeyboard(e) {
    switch (e.keyCode) {
        case 37: // Left arrow
        case 38: // Up arrow
            e.preventDefault();
            window.termosNavigation.prev();
            break;
        case 39: // Right arrow  
        case 40: // Down arrow
            e.preventDefault();
            window.termosNavigation.next();
            break;
        case 13: // Enter
            e.preventDefault();
            window.termosNavigation.select();
            break;
    }
}

function carregarTermosConteudo() {
    var termosTexto = document.getElementById('termosTexto');
    
    // Termos básicos (podem vir de API futuramente)
    var conteudo = `
        <h3>Termos de Uso do NomaTV</h3>
        <p>Ao utilizar este aplicativo, você concorda com os seguintes termos:</p>
        <ul>
            <li>Este aplicativo é destinado apenas para uso pessoal e doméstico</li>
            <li>É proibido o uso comercial ou redistribuição do conteúdo</li>
            <li>O usuário é responsável pela veracidade dos dados informados</li>
            <li>Nos reservamos o direito de suspender o acesso em caso de uso inadequado</li>
        </ul>
        
        <h3>Política de Privacidade</h3>
        <p>Seus dados pessoais serão tratados conforme:</p>
        <ul>
            <li>Coletamos apenas dados necessários para funcionamento do serviço</li>
            <li>Não compartilhamos dados com terceiros sem autorização</li>
            <li>Dados ficam armazenados localmente no dispositivo</li>
        </ul>
    `;
    
    termosTexto.innerHTML = conteudo;
}

function aceitarTermos() {
    console.log('[TERMOS] Usuário aceitou os termos');
    
    try {
        // Persistir aceite com timestamp
        localStorage.setItem('aceitouTermos', 'true');
        localStorage.setItem('dataAceiteTermos', Date.now().toString());
        
        // Feedback visual
        updateStatus('Termos aceitos com sucesso!', 100, 'success');
        
        // Navegar para próxima tela após delay
        setTimeout(function() {
            window.nav('index');
        }, 1000);
        
    } catch (e) {
        console.error('[TERMOS] Erro ao salvar aceite:', e);
        updateStatus('Erro ao salvar preferências', 0, 'error');
    }
}

function recusarTermos() {
    console.log('[TERMOS] Usuário recusou os termos');
    
    // Feedback visual
    updateStatus('Termos recusados', 0, 'warning');
    
    // Navegar para tela de despedida
    setTimeout(function() {
        window.nav('decline');
    }, 1000);
}

function updateStatus(message, progress, type) {
    var statusEl = document.getElementById('statusMessage');
    var progressEl = document.getElementById('progressBar');
    
    if (statusEl) {
        statusEl.textContent = message;
        statusEl.className = 'status-message status-' + (type || 'info');
    }
    
    if (progressEl && progress !== undefined) {
        progressEl.style.width = progress + '%';
    }
}

// Cleanup ao sair da sessão
function cleanupTermos() {
    document.removeEventListener('keydown', handleTermosKeyboard);
    window.termosNavigation = null;
}

window.cleanupCurrentSession = cleanupTermos;
```

### **🎨 Layout Termos:**
```css
.termos-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-family: 'Arial', sans-serif;
}

.termos-header {
    text-align: center;
    padding: 40px;
}

.termos-content {
    flex: 1;
    padding: 20px 60px;
    overflow-y: auto;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    margin: 0 40px;
    border-radius: 15px;
}

.termos-actions {
    display: flex;
    justify-content: center;
    gap: 40px;
    padding: 40px;
}

.termo-btn {
    background: transparent;
    border: 2px solid white;
    color: white;
    padding: 15px 40px;
    border-radius: 25px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 150px;
}

.termo-btn.lg-focused {
    background: #AEFF00;
    color: #000;
    border-color: #AEFF00;
    transform: scale(1.1);
    box-shadow: 0 0 20px rgba(174, 255, 0, 0.5);
}

.termo-btn.aceitar:hover {
    background: #28a745;
    border-color: #28a745;
}

.termo-btn.recusar:hover {
    background: #dc3545;
    border-color: #dc3545;
}
```

### **🗂️ Storage:**
```javascript
// localStorage (persistente)
'aceitouTermos' → 'true' | null
'dataAceiteTermos' → timestamp
```

---

## ❌ **Decline.html**

### **📁 Arquivo:**
- `decline.html` (Tela de despedida por recusa de termos)

### **🎯 Responsabilidades:**
- Exibir mensagem educativa sobre recusa
- Opção de reconsideração
- Opção de saída definitiva
- Interface empática e não agressiva

### **🔄 Fluxo de Inicialização:**
```javascript
function inicializarDecline() {
    console.log('[DECLINE] Inicializando tela de despedida...');
    
    // Resolver elementos DOM
    var btnReconsiderar = document.getElementById('btnReconsiderar');
    var btnSair = document.getElementById('btnSair');
    
    if (!btnReconsiderar || !btnSair) {
        console.error('[DECLINE] Elementos DOM não encontrados');
        return;
    }
    
    // Aplicar foco inicial no reconsiderar (ação positiva)
    btnReconsiderar.focus();
    btnReconsiderar.classList.add('lg-focused');
    
    // Setup navegação
    setupDeclineNavigation();
    
    // Setup handlers
    btnReconsiderar.addEventListener('click', reconsiderarTermos);
    btnSair.addEventListener('click', sairAplicativo);
    
    // Keyboard navigation
    document.addEventListener('keydown', handleDeclineKeyboard);
    
    // Log evento (pode ser útil para analytics)
    logDeclineEvent();
}

function setupDeclineNavigation() {
    var botoes = document.querySelectorAll('.decline-btn');
    var currentFocus = 0;
    
    function updateFocus() {
        for (var i = 0; i < botoes.length; i++) {
            botoes[i].classList.remove('lg-focused');
        }
        if (botoes[currentFocus]) {
            botoes[currentFocus].classList.add('lg-focused');
            botoes[currentFocus].focus();
        }
    }
    
    window.declineNavigation = {
        next: function() {
            currentFocus = (currentFocus + 1) % botoes.length;
            updateFocus();
        },
        prev: function() {
            currentFocus = (currentFocus - 1 + botoes.length) % botoes.length;
            updateFocus();
        },
        select: function() {
            if (botoes[currentFocus]) {
                botoes[currentFocus].click();
            }
        }
    };
}

function handleDeclineKeyboard(e) {
    switch (e.keyCode) {
        case 37: // Left arrow
        case 38: // Up arrow
            e.preventDefault();
            window.declineNavigation.prev();
            break;
        case 39: // Right arrow
        case 40: // Down arrow
            e.preventDefault();
            window.declineNavigation.next();
            break;
        case 13: // Enter
            e.preventDefault();
            window.declineNavigation.select();
            break;
        case 461: // Back button TV
        case 27: // ESC
            e.preventDefault();
            reconsiderarTermos(); // Ação padrão positiva
            break;
    }
}

function reconsiderarTermos() {
    console.log('[DECLINE] Usuário escolheu reconsiderar');
    
    // Feedback positivo
    updateStatus('Redirecionando para termos...', 50, 'info');
    
    // Voltar para termos após delay
    setTimeout(function() {
        window.nav('termos');
    }, 1000);
}

function sairAplicativo() {
    console.log('[DECLINE] Usuário escolheu sair definitivamente');
    
    // Limpar qualquer dado local
    try {
        localStorage.removeItem('aceitouTermos');
        localStorage.removeItem('dataAceiteTermos');
        sessionStorage.clear();
    } catch (e) {
        console.warn('[DECLINE] Erro ao limpar dados:', e);
    }
    
    // Feedback final
    updateStatus('Encerrando aplicativo...', 100, 'info');
    
    // Tentar fechar o app (se suportado pela plataforma)
    setTimeout(function() {
        if (window.close) {
            window.close();
        } else if (navigator.app && navigator.app.exitApp) {
            navigator.app.exitApp();
        } else {
            // Fallback: mostrar tela final
            mostrarTelaFinal();
        }
    }, 2000);
}

function mostrarTelaFinal() {
    document.body.innerHTML = `
        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; background: #333; color: white; text-align: center;">
            <h1>Obrigado!</h1>
            <p>Você pode fechar esta janela agora.</p>
            <p style="margin-top: 40px; opacity: 0.7; font-size: 14px;">NomaTV v2.0</p>
        </div>
    `;
}

function logDeclineEvent() {
    try {
        var declineLog = {
            timestamp: Date.now(),
            userAgent: navigator.userAgent,
            resolution: screen.width + 'x' + screen.height
        };
        
        // Pode enviar para analytics se necessário
        localStorage.setItem('last_decline_event', JSON.stringify(declineLog));
        
    } catch (e) {
        console.warn('[DECLINE] Erro ao logar evento:', e);
    }
}

function updateStatus(message, progress, type) {
    var statusEl = document.getElementById('statusMessage');
    var progressEl = document.getElementById('progressBar');
    
    if (statusEl) {
        statusEl.textContent = message;
        statusEl.className = 'status-message status-' + (type || 'info');
    }
    
    if (progressEl && progress !== undefined) {
        progressEl.style.width = progress + '%';
    }
}

// Cleanup
function cleanupDecline() {
    document.removeEventListener('keydown', handleDeclineKeyboard);
    window.declineNavigation = null;
}

window.cleanupCurrentSession = cleanupDecline;
```

### **🎨 Layout Decline:**
```css
.decline-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    font-family: 'Arial', sans-serif;
}

.decline-header {
    text-align: center;
    padding: 60px 40px 40px;
}

.decline-header h1 {
    font-size: 48px;
    margin-bottom: 20px;
    font-weight: 300;
}

.decline-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 40px;
    text-align: center;
}

.decline-message {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 20px;
    max-width: 600px;
    margin-bottom: 40px;
}

.decline-message h2 {
    margin-bottom: 20px;
    font-weight: 400;
}

.decline-message p {
    line-height: 1.6;
    font-size: 18px;
    opacity: 0.9;
}

.decline-actions {
    display: flex;
    gap: 30px;
}

.decline-btn {
    background: transparent;
    border: 2px solid white;
    color: white;
    padding: 15px 30px;
    border-radius: 25px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 180px;
}

.decline-btn.lg-focused {
    background: #AEFF00;
    color: #000;
    border-color: #AEFF00;
    transform: scale(1.1);
    box-shadow: 0 0 20px rgba(174, 255, 0, 0.5);
}

.decline-btn.reconsiderar:hover {
    background: #28a745;
    border-color: #28a745;
}

.decline-btn.sair:hover {
    background: rgba(255, 255, 255, 0.2);
}
```

### **🗂️ Storage:**
```javascript
// localStorage (limpeza)
remove: 'aceitouTermos', 'dataAceiteTermos'
// sessionStorage (limpeza total)
clear()
```

---

## 🔑 **Login.html**

### **📁 Arquivo:**
- `login.html` (Captura de credenciais do usuário)

### **🎯 Responsabilidades:**
- Interface de login para credenciais IPTV
- Validação de campos obrigatórios
- Comunicação com backend (validar_login.php)
- Armazenamento temporário em sessionStorage
- Navegação para autenticando em caso de sucesso

### **🔄 Fluxo de Inicialização:**
```javascript
function inicializarLogin() {
    console.log('[LOGIN] Inicializando tela de login...');
    
    // Verificar se já está autenticado
    var authenticated = localStorage.getItem('authenticated');
    if (authenticated === 'true') {
        console.log('[LOGIN] Usuário já autenticado - redirecionando');
        window.nav('home');
        return;
    }
    
    // Resolver elementos DOM
    var formLogin = document.getElementById('loginForm');
    var inputProvedor = document.getElementById('provedor');
    var inputUsuario = document.getElementById('usuario');
    var inputSenha = document.getElementById('senha');
    var btnLogin = document.getElementById('btnLogin');
    
    if (!formLogin || !inputProvedor || !inputUsuario || !inputSenha || !btnLogin) {
        console.error('[LOGIN] Elementos DOM críticos não encontrados');
        return;
    }
    
    // Setup foco inicial
    inputProvedor.focus();
    inputProvedor.classList.add('lg-focused');
    
    // Setup navegação por controle
    setupLoginNavigation();
    
    // Setup handlers
    formLogin.addEventListener('submit', handleLoginSubmit);
    btnLogin.addEventListener('click', handleLoginSubmit);
    
    // Keyboard navigation
    document.addEventListener('keydown', handleLoginKeyboard);
    
    // Pré-preencher se houver dados salvos
    preencherDadosSalvos();
    
    // Auto-foco management
    setupAutoFocus();
}

function setupLoginNavigation() {
    var campos = [
        document.getElementById('provedor'),
        document.getElementById('usuario'), 
        document.getElementById('senha'),
        document.getElementById('btnLogin')
    ];
    
    var currentFocus = 0;
    
    function updateFocus() {
        // Remove foco de todos
        for (var i = 0; i < campos.length; i++) {
            if (campos[i]) {
                campos[i].classList.remove('lg-focused');
            }
        }
        
        // Aplica foco no atual
        if (campos[currentFocus]) {
            campos[currentFocus].classList.add('lg-focused');
            campos[currentFocus].focus();
        }
    }
    
    window.loginNavigation = {
        next: function() {
            currentFocus = (currentFocus + 1) % campos.length;
            updateFocus();
        },
        prev: function() {
            currentFocus = (currentFocus - 1 + campos.length) % campos.length;
            updateFocus();
        },
        getCurrentField: function() {
            return campos[currentFocus];
        }
    };
}

function handleLoginKeyboard(e) {
    var currentField = window.loginNavigation.getCurrentField();
    
    switch (e.keyCode) {
        case 38: // Up arrow
            e.preventDefault();
            window.loginNavigation.prev();
            break;
        case 40: // Down arrow
        case 9:  // Tab
            e.preventDefault();
            window.loginNavigation.next();
            break;
        case 13: // Enter
            if (currentField && currentField.id === 'btnLogin') {
                e.preventDefault();
                handleLoginSubmit(e);
            } else if (currentField && currentField.type !== 'button') {
                e.preventDefault();
                window.loginNavigation.next();
            }
            break;
    }
}

function preencherDadosSalvos() {
    // Preencher com último provedor usado (se houver)
    var ultimoProvedor = localStorage.getItem('ultimo_provedor');
    if (ultimoProvedor) {
        document.getElementById('provedor').value = ultimoProvedor;
    }
    
    // Preencher usuário se salvo (opcional - por segurança pode não salvar)
    var ultimoUsuario = localStorage.getItem('ultimo_usuario');
    if (ultimoUsuario) {
        document.getElementById('usuario').value = ultimoUsuario;
    }
}

function setupAutoFocus() {
    var inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
    
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('focus', function() {
            this.classList.add('lg-focused');
        });
        
        inputs[i].addEventListener('blur', function() {
            this.classList.remove('lg-focused');
        });
    }
}

function handleLoginSubmit(e) {
    if (e) e.preventDefault();
    
    console.log('[LOGIN] Iniciando processo de login...');
    
    // Coletar dados do form
    var loginData = coletarDadosLogin();
    
    // Validar campos
    if (!validarCampos(loginData)) {
        return;
    }
    
    // Desabilitar form durante processo
    desabilitarForm(true);
    
    // Mostrar loader
    updateStatus('Validando credenciais...', 20, 'info');
    
    // Chamar backend
    enviarCredenciaisBackend(loginData);
}

function coletarDadosLogin() {
    return {
        provedor: document.getElementById('provedor').value.trim(),
        username: document.getElementById('usuario').value.trim(),
        password: document.getElementById('senha').value.trim(),
        client_id: window.getOrCreateClientId()
    };
}

function validarCampos(data) {
    // Validar provedor
    if (!data.provedor) {
        updateStatus('Nome do provedor é obrigatório', 0, 'error');
        document.getElementById('provedor').focus();
        return false;
    }
    
    // Validar usuário
    if (!data.username) {
        updateStatus('Nome de usuário é obrigatório', 0, 'error');
        document.getElementById('usuario').focus();
        return false;
    }
    
    // Validar senha
    if (!data.password) {
        updateStatus('Senha é obrigatória', 0, 'error');
        document.getElementById('senha').focus();
        return false;
    }
    
    // Validar client_id
    if (!data.client_id) {
        updateStatus('Erro interno: ID do cliente não gerado', 0, 'error');
        return false;
    }
    
    return true;
}

function enviarCredenciaisBackend(loginData) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'https://webnoma.space/api/validar_login.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.timeout = 15000;
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                processarRespostaLogin(response, loginData);
            } catch (e) {
                console.error('[LOGIN] Erro parse resposta:', e);
                updateStatus('Erro na resposta do servidor', 0, 'error');
                desabilitarForm(false);
            }
        } else {
            console.error('[LOGIN] HTTP erro:', xhr.status);
            updateStatus('Erro de conexão com servidor', 0, 'error');
            desabilitarForm(false);
        }
    };
    
    xhr.onerror = function() {
        console.error('[LOGIN] Erro de rede');
        updateStatus('Erro de conexão', 0, 'error');
        desabilitarForm(false);
    };
    
    xhr.ontimeout = function() {
        console.error('[LOGIN] Timeout na validação');
        updateStatus('Timeout na validação - tente novamente', 0, 'error');
        desabilitarForm(false);
    };
    
    updateStatus('Enviando dados...', 40, 'info');
    xhr.send(JSON.stringify(loginData));
}

function processarRespostaLogin(response, loginData) {
    if (response.success && response.data) {
        // Sucesso - salvar dados temporários para autenticando
        updateStatus('Credenciais validadas!', 80, 'success');
        
        try {
            // Salvar 5 variáveis no sessionStorage
            sessionStorage.setItem('temp_dns', response.data.dns);
            sessionStorage.setItem('temp_provedor', response.data.provedor);
            sessionStorage.setItem('temp_usuario', response.data.username);
            sessionStorage.setItem('temp_senha', response.data.password);
            sessionStorage.setItem('temp_revendedor_id', response.data.revendedor_id);
            
            // Salvar dados para próximo login (opcionais)
            localStorage.setItem('ultimo_provedor', loginData.provedor);
            localStorage.setItem('ultimo_usuario', loginData.username);
            
            updateStatus('Redirecionando...', 100, 'success');
            
            // Navegar para autenticando
            setTimeout(function() {
                window.nav('autenticando');
            }, 1000);
            
        } catch (e) {
            console.error('[LOGIN] Erro ao salvar dados temporários:', e);
            updateStatus('Erro ao salvar dados - tente novamente', 0, 'error');
            desabilitarForm(false);
        }
        
    } else {
        // Falha na validação
        var errorMsg = response.message || 'Credenciais inválidas';
        updateStatus(errorMsg, 0, 'error');
        desabilitarForm(false);
        
        // Focar no primeiro campo para retry
        document.getElementById('provedor').focus();
    }
}

function desabilitarForm(disabled) {
    var inputs = document.querySelectorAll('#loginForm input, #loginForm button');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].disabled = disabled;
    }
    
    var btnLogin = document.getElementById('btnLogin');
    if (btnLogin) {
        btnLogin.textContent = disabled ? 'Validando...' : 'Entrar';
    }
}

function updateStatus(message, progress, type) {
    var statusEl = document.getElementById('statusMessage');
    var progressEl = document.getElementById('progressBar');
    
    if (statusEl) {
        statusEl.textContent = message;
        statusEl.className = 'status-message status-' + (type || 'info');
    }
    
    if (progressEl && progress !== undefined) {
        progressEl.style.width = progress + '%';
    }
}

// Cleanup
function cleanupLogin() {
    document.removeEventListener('keydown', handleLoginKeyboard);
    window.loginNavigation = null;
}

window.cleanupCurrentSession = cleanupLogin;
```

### **🎨 Layout Login:**
```css
.login-container {
    display: flex;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Arial', sans-serif;
}

.login-left {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.login-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 40px 80px;
    background: rgba(255, 255, 255, 0.95);
    color: #333;
}

.login-logo {
    text-align: center;
    margin-bottom: 40px;
}

.login-logo img {
    max-width: 200px;
    height: auto;
}

.login-form {
    width: 100%;
    max-width: 400px;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #555;
}

.form-input {
    width: 100%;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-input:focus,
.form-input.lg-focused {
    outline: none;
    border-color: #AEFF00;
    box-shadow: 0 0 10px rgba(174, 255, 0, 0.3);
}

.form-input:disabled {
    background: #f5f5f5;
    opacity: 0.7;
}

.login-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.login-btn:hover,
.login-btn.lg-focused {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.login-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.login-info {
    color: white;
    text-align: center;
}

.login-info h1 {
    font-size: 48px;
    margin-bottom: 20px;
    font-weight: 300;
}

.login-info p {
    font-size: 20px;
    line-height: 1.6;
    opacity: 0.9;
}

.status-message {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
    font-weight: bold;
}

.status-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.status-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.status-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.status-info { background: #d1ecf1; color: #0c5460; border: 1px solid #b6ebf0; }

.progress-container {
    width: 100%;
    height: 4px;
    background: #eee;
    border-radius: 2px;
    margin-bottom: 20px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
    transition: width 0.3s ease;
    width: 0%;
}
```

### **🗂️ Storage Strategy:**
```javascript
// sessionStorage (temporário para autenticando)
'temp_dns' → string
'temp_provedor' → string  
'temp_usuario' → string
'temp_senha' → string
'temp_revendedor_id' → string

// localStorage (persistente - conveniência)
'ultimo_provedor' → string
'ultimo_usuario' → string (opcional por segurança)
'client_id' → UUID v4
```

### **⚠️ Error Handling:**
- **Campos vazios:** Focus no campo + mensagem específica
- **Erro backend:** Retry automático + mensagem clara  
- **Timeout:** Mensagem + reabilitação do form
- **Network error:** Fallback + instrução ao usuário

---ponte-frontend-backend)
16. [🎭 Sistema Branding Painéis](#-sistema-branding-painéis)
17. [🔍 Filtros & Paginação](#-filtros--paginação)

### 🌐 SEÇÃO 3: BACKEND COMPLETO
18. [🗃️ Estrutura de Tabelas](#️-estrutura-de-tabelas)
19. [🔐 Sistema de Autenticação](#-sistema-de-autenticação)
20. [📍 Endpoints Completos](#-endpoints-completos)
21. [🌳 Sistema Hierárquico](#-sistema-hierárquico)
22. [💰 Sistema Financeiro](#-sistema-financeiro)
23. [📊 Stats & Dashboard](#-stats--dashboard)
24. [🗄️ MySQL Produção](#️-mysql-produção)
25. [🚀 Deploy & Configuração](#-deploy--configuração)

---

# 📱 SEÇÃO 1: APP SMART TV

## 🚀 **Index Casca + Server.js**

### **📁 Arquivos:**
- `index.html` (Casca ~100 linhas)
- `server.js` (Core auto-injetável)

### **🎯 Responsabilidades:**

#### **Index.html (Casca):**
- Logo NomaApp (PNG + fallback CSS)
- Spinner profissional Netflix-style
- Storage híbrido init (localStorage + sessionStorage + IndexedDB)
- HLS.js + Video.js (players críticos)
- Duplo fetch com timeout (principal + backup)
- Tratamento erro rede (botão focável)
- Auto-injeção do server.js

#### **Server.js (Core):**
- 21 funções globais (nav, storage, auth, branding)
- Sistema SPA completo
- Estados hierárquicos + botão voltar
- Cleanup automático
- ES5 puro (compatibilidade total)

### **🔗 URLs de Deploy:**
- **Principal:** `https://webnoma.space/player/server.js`
- **Backup:** `https://webnoma.shop/player/server.js`

### **⚡ Fluxo de Inicialização:**
```
LG WebOS → index.html casca → Storage híbrido init → 
Duplo fetch server.js → Auto-injeção + execução → 
carregarSessao inicial
```

### **🗂️ Storage Híbrido (4 Camadas):**

#### **localStorage (1ms - Dados Críticos):**
- Credenciais: usuario, senha, dns, provedor
- Configurações: volume, qualidade, idioma, parental
- IDs únicos: client_id, revendedor_id
- Estado persistente: ultima_sessao, aceitouTermos

#### **sessionStorage (2ms - Sessão Atual):**
- Estado UI: foco_atual, pagina_ativa, modal_aberto
- Navegação: historico_navegacao, breadcrumb
- Cache rápido: epg_canal_atual, categoria_ativa
- Player: posicao_atual, volume_temporario

#### **IndexedDB (10ms - Dados Pesados):**
- **STORES PERSISTENTES:** user_persistent, player_assets
- **STORES PRÉ-CARREGADOS:** preload_cache (EPG, home, categorias)

#### **Memory Cache (0.1ms - Voláteis):**
- Busca temporária, buffer navegação, estados transitórios

---

## 🔐 **Autenticando.html**

### **📁 Arquivo:**
- `autenticando.html` (Sistema de pré-carregamento sequencial)

### **🎯 Responsabilidades:**
- Validação de credenciais Xtream Codes
- Pré-carregamento sequencial com fallback obrigatório
- Salvamento de assets críticos no IndexedDB
- Cache duplo (sessionStorage + memory) para performance
- Navegação automática para Home (sempre finaliza)

### **🔄 Fluxo Sequencial (6 Etapas - Validação + Preload):**

#### **0. validateCredentialsFromLogin() - Etapa Inicial:**
```javascript
function validateCredentialsFromLogin() {
    try {
        // Ler credenciais do sessionStorage (deixadas pelo login)
        var usuario = sessionStorage.getItem('temp_usuario') || localStorage.getItem('usuario');
        var senha = sessionStorage.getItem('temp_senha') || localStorage.getItem('senha');
        var dns = sessionStorage.getItem('temp_dns') || localStorage.getItem('dns');
        var provedor = sessionStorage.getItem('temp_provedor') || localStorage.getItem('provedor');
        var revendedorId = sessionStorage.getItem('temp_revendedor_id');
        
        if (!usuario || !senha || !dns) {
            console.error('[AUTH] Credenciais incompletas');
            updateStatus('Credenciais inválidas', 0, 'error');
            setTimeout(function() { window.nav('login'); }, 2000);
            return;
        }
        
        // Montar URL completa de teste Xtream
        var baseUrl = String(dns).replace(/\/+$/, '');
        var testUrl = baseUrl + '/player_api.php?username=' + 
                     encodeURIComponent(usuario) + 
                     '&password=' + encodeURIComponent(senha) + 
                     '&action=get_live_categories';
        
        updateStatus('Validando credenciais...', 5);
        
        // Teste real na API Xtream
        window.makeXHRRequest(testUrl,
            function(response) { // Sucesso - credenciais válidas
                try {
                    var data = JSON.parse(response);
                    if (Array.isArray(data) && data.length > 0) {
                        updateStatus('Credenciais validadas', 10, 'success');
                        
                        // PROMOVER DADOS PARA LOCALSTORAGE (OBRIGATÓRIO)
                        promoverDadosParaLocalStorage(usuario, senha, dns, provedor, revendedorId);
                        
                    } else {
                        throw new Error('Resposta inválida da API');
                    }
                } catch (parseErr) {
                    console.error('[AUTH] Erro parse validação:', parseErr);
                    updateStatus('Dados de resposta inválidos', 0, 'error');
                    setTimeout(function() { window.nav('login'); }, 2000);
                }
            },
            function(error) { // Falha - credenciais inválidas
                console.error('[AUTH] Falha validação Xtream:', error);
                updateStatus('Credenciais inválidas ou servidor indisponível', 0, 'error');
                
                // Limpar credenciais inválidas
                limparSessionStorage();
                setTimeout(function() { window.nav('login'); }, 2000);
            },
            10000 // timeout 10s
        );
    } catch (e) {
        console.error('[AUTH] Erro geral validação:', e);
        updateStatus('Erro interno de validação', 0, 'error');
        setTimeout(function() { window.nav('login'); }, 2000);
    }
}

function promoverDadosParaLocalStorage(usuario, senha, dns, provedor, revendedorId) {
    try {
        // SALVAR PERMANENTEMENTE NO LOCALSTORAGE
        localStorage.setItem('usuario', usuario);
        localStorage.setItem('senha', senha);
        localStorage.setItem('dns', dns);
        localStorage.setItem('provedor', provedor);
        if (revendedorId) {
            localStorage.setItem('revendedor_id', revendedorId);
        }
        localStorage.setItem('authenticated', 'true');
        localStorage.setItem('auth_timestamp', Date.now().toString());
        
        console.log('[AUTH] Dados promovidos para localStorage com sucesso');
        
        // Limpar sessionStorage após promoção
        limparSessionStorage();
        
        // INICIAR PRÉ-CARREGAMENTO (SÓ APÓS SALVAR)
        setTimeout(function() {
            startPreloadSequence();
        }, 500);
        
    } catch (e) {
        console.error('[AUTH] Erro ao promover dados:', e);
        updateStatus('Erro ao salvar dados', 0, 'error');
        setTimeout(function() { window.nav('login'); }, 2000);
    }
}

function limparSessionStorage() {
    try {
        sessionStorage.removeItem('temp_usuario');
        sessionStorage.removeItem('temp_senha');
        sessionStorage.removeItem('temp_dns');
        sessionStorage.removeItem('temp_provedor');
        sessionStorage.removeItem('temp_revendedor_id');
    } catch (e) {
        console.warn('[AUTH] Erro ao limpar sessionStorage:', e);
    }
}

function startPreloadSequence() {
    // SEQUENCIAL OBRIGATÓRIO - SÓ PRÓXIMA SE ANTERIOR TERMINAR
    preloadAll(function(success) {
        finishAuthentication();
        // Sempre navegar para home no final
        setTimeout(function() {
            window.nav('home');
        }, 500);
    });
}
```

### **🔄 Sequência Obrigatória (Só próxima se anterior terminar):**
```javascript
function preloadAll(callback) {
    var errors = [];
    var startTime = Date.now();
    
    // ETAPA 1: Players (obrigatório completar)
    updateStatus('Carregando players', 10);
    preloadPlayers(function(ok1) {
        if (!ok1) {
            console.warn('[PRELOAD] Players falharam - continuando');
            errors.push('players');
        }
        
        // ETAPA 2: Categorias (obrigatório completar)
        updateStatus('Carregando categorias', 30);
        preloadCategoriesSequence(function(ok2) {
            if (!ok2) {
                console.warn('[PRELOAD] Categorias falharam - continuando');
                errors.push('categories');
            }
            
            // ETAPA 3: Channels (obrigatório completar)
            updateStatus('Carregando canais', 60);
            fetchAndSaveChannelsList(function(ok3) {
                if (!ok3) {
                    console.warn('[PRELOAD] Channels falharam - continuando');
                    errors.push('channels');
                }
                
                // ETAPA 4: Home cache (obrigatório completar)
                updateStatus('Preparando interface', 70);
                preLoadContent(function(ok4) {
                    if (!ok4) {
                        console.warn('[PRELOAD] Home cache falhou - continuando');
                        errors.push('home');
                    }
                    
                    // ETAPA 5: Dados Persistentes IndexedDB (obrigatório completar)
                    updateStatus('Preparando dados pessoais', 75);
                    initializeUserPersistentData(function(ok5) {
                        if (!ok5) {
                            console.warn('[PRELOAD] Dados persistentes falharam - continuando');
                            errors.push('persistent');
                        }
                        
                        // ETAPA 6: EPG (final - pode falhar)
                        updateStatus('Carregando guia de programação', 80);
                        fetchAndSaveEPG(function(ok6) {
                            var totalTime = Date.now() - startTime;
                            
                            if (!ok6) {
                                console.error('[PRELOAD] EPG falhou após', totalTime, 'ms');
                                errors.push('epg');
                                updateStatus('Erro ao carregar guia de programação', 100, 'warning');
                            } else {
                                updateStatus('Carregamento concluído', 100, 'success');
                            }
                            
                            console.log('[PRELOAD] Concluído em', totalTime, 'ms, erros:', errors);
                            callback(errors.length < 3); // Sucesso se menos de 3 erros
                        });
                    });
                });
            });
        });
    });
}
```

### **💾 Sistema de Dados Persistentes (IndexedDB):**
```javascript
function initializeUserPersistentData(callback) {
    try {
        // Inicializar stores persistentes do usuário
        var stores = [
            'user_favorites',      // Favoritos (filmes, séries, canais)
            'user_progress',       // Progresso de reprodução (estilo Netflix)
            'user_watchlist',      // Lista para assistir depois
            'user_preferences'     // Preferências do usuário
        ];
        
        var completedStores = 0;
        var errors = [];
        
        function checkComplete() {
            completedStores++;
            if (completedStores >= stores.length) {
                callback(errors.length === 0);
            }
        }
        
        // Inicializar cada store
        for (var i = 0; i < stores.length; i++) {
            initializeStore(stores[i], function(storeName, success) {
                if (!success) {
                    errors.push(storeName);
                }
                checkComplete();
            });
        }
        
    } catch (e) {
        console.error('[PERSISTENT] Erro inicialização:', e);
        callback(false);
    }
}

function initializeStore(storeName, callback) {
    // Verificar se store já existe
    window.idb.get('user_persistent', storeName, function(err, existing) {
        if (!err && existing) {
            // Store já existe
            console.log('[PERSISTENT] Store', storeName, 'já inicializado');
            callback(storeName, true);
        } else {
            // Criar store inicial
            var initialData = getInitialStoreData(storeName);
            window.idb.put('user_persistent', storeName, initialData, function(putErr) {
                console.log('[PERSISTENT] Store', storeName, putErr ? 'falhou' : 'criado');
                callback(storeName, !putErr);
            });
        }
    });
}

function getInitialStoreData(storeName) {
    switch (storeName) {
        case 'user_favorites':
            return {
                movies: [],     // IDs de filmes favoritos
                series: [],     // IDs de séries favoritas
                channels: [],   // IDs de canais favoritos
                updated_at: Date.now()
            };
        
        case 'user_progress':
            return {
                // Formato: "movie_123": { position: 1847, duration: 5400, percentage: 34.2, last_watched: timestamp }
                // Formato: "series_456_s1_e3": { position: 892, duration: 2700, percentage: 33.0, last_watched: timestamp }
                items: {},
                updated_at: Date.now()
            };
        
        case 'user_watchlist':
            return {
                movies: [],     // { id, added_at, type: 'movie' }
                series: [],     // { id, added_at, type: 'series' }
                updated_at: Date.now()
            };
        
        case 'user_preferences':
            return {
                audio_language: 'pt',
                subtitle_language: 'pt',
                video_quality: 'auto',
                autoplay_next: true,
                skip_intro: true,
                updated_at: Date.now()
            };
        
        default:
            return { created_at: Date.now() };
    }
}
```

### **📍 Funções de Dados Persistentes (Estilo Netflix):**
```javascript
// Salvar progresso de filme/série
function saveWatchProgress(contentType, contentId, seasonNum, episodeNum, position, duration) {
    var key = contentType === 'movie' ? 
              'movie_' + contentId : 
              'series_' + contentId + '_s' + seasonNum + '_e' + episodeNum;
    
    var progressData = {
        position: position,           // Posição em segundos
        duration: duration,          // Duração total em segundos
        percentage: (position / duration) * 100,
        last_watched: Date.now(),
        content_type: contentType,
        content_id: contentId
    };
    
    if (contentType === 'series') {
        progressData.season = seasonNum;
        progressData.episode = episodeNum;
    }
    
    // Atualizar IndexedDB
    window.idb.get('user_persistent', 'user_progress', function(err, currentData) {
        if (err || !currentData) currentData = { items: {}, updated_at: Date.now() };
        
        currentData.items[key] = progressData;
        currentData.updated_at = Date.now();
        
        window.idb.put('user_persistent', 'user_progress', currentData, function(putErr) {
            if (!putErr) {
                console.log('[PROGRESS] Salvo:', key, Math.round(progressData.percentage) + '%');
            }
        });
    });
}

// Recuperar progresso
function getWatchProgress(contentType, contentId, seasonNum, episodeNum, callback) {
    var key = contentType === 'movie' ? 
              'movie_' + contentId : 
              'series_' + contentId + '_s' + seasonNum + '_e' + episodeNum;
    
    window.idb.get('user_persistent', 'user_progress', function(err, data) {
        if (!err && data && data.items && data.items[key]) {
            callback(null, data.items[key]);
        } else {
            callback(new Error('Progresso não encontrado'), null);
        }
    });
}

// Adicionar aos favoritos
function addToFavorites(contentType, contentId, callback) {
    window.idb.get('user_persistent', 'user_favorites', function(err, data) {
        if (err || !data) data = { movies: [], series: [], channels: [], updated_at: Date.now() };
        
        var list = data[contentType + 's'] || [];
        if (list.indexOf(contentId) === -1) {
            list.push(contentId);
            data.updated_at = Date.now();
            
            window.idb.put('user_persistent', 'user_favorites', data, function(putErr) {
                if (callback) callback(!putErr);
            });
        } else {
            if (callback) callback(true); // Já existe
        }
    });
}

// Verificar se é favorito
function isFavorite(contentType, contentId, callback) {
    window.idb.get('user_persistent', 'user_favorites', function(err, data) {
        if (!err && data && data[contentType + 's']) {
            var isFav = data[contentType + 's'].indexOf(contentId) !== -1;
            callback(null, isFav);
        } else {
            callback(null, false);
        }
    });
}
```

#### **1. preloadPlayers() - 8s timeout:**
```javascript
function preloadPlayers(callback) {
    var PLAYER_VERSION = 'v1.0';
    
    // Verificar cache existente
    window.idb.get('player_libs', 'current', function(err, stored) {
        if (!err && stored && stored.version === PLAYER_VERSION) {
            console.log('[PRELOAD] Players já em cache');
            return callback(true);
        }
        
        // URLs configuráveis (localStorage)
        var libsUrls = JSON.parse(localStorage.getItem('player_libs_urls') || '[]');
        if (!libsUrls.length) {
            console.warn('[PRELOAD] Nenhuma URL de player configurada');
            return callback(false); // fallback - continua
        }
        
        var downloaded = [];
        var completed = 0;
        
        function downloadLib(url) {
            window.makeXHRRequest(url,
                function(content) { // sucesso
                    downloaded.push({url: url, content: content});
                    checkComplete();
                },
                function(error) { // erro - não bloqueia
                    console.warn('[PRELOAD] Falha ao baixar:', url);
                    checkComplete();
                },
                8000 // timeout 8s
            );
        }
        
        function checkComplete() {
            completed++;
            if (completed >= libsUrls.length) {
                if (downloaded.length > 0) {
                    // Salvar no IndexedDB
                    var payload = {
                        data: downloaded,
                        version: PLAYER_VERSION,
                        ts: Date.now()
                    };
                    window.idb.put('player_libs', 'current', payload, function(errPut) {
                        if (!errPut) {
                            window.storage.temporary.set('player_libs', downloaded, 24*60*60*1000);
                        }
                        callback(!errPut);
                    });
                } else {
                    callback(false); // fallback - continua
                }
            }
        }
        
        // Baixar todas as libs
        for (var i = 0; i < libsUrls.length; i++) {
            downloadLib(libsUrls[i]);
        }
    });
}
```

### **🗂️ Storage Strategy Detalhada:**

#### **IndexedDB (Dados Pesados):**
```javascript
// Players (libs baixadas)
('player_libs', 'current') → {
    data: [{url: '...', content: '...'}],
    version: 'v1.0',
    ts: timestamp
}

// EPG completo (XML)
('epg', 'current') → {
    data: '<xmltv>...</xmltv>',
    provedor: 'provider_name',
    ts: timestamp,
    expires: timestamp + 6h
}

// Channels (streams)
('live_streams', 'minimal') → [{stream_id, name, category_id}]
('live_streams', 'complete') → [full_stream_objects]
```

---

## 📄 **Index Corpo**

### **📁 Arquivo:**
- `index-corpo.html` (Tela de entrada + validação + pré-carregamento curto)

### **🎯 Responsabilidades:**
- UI de inicialização (logo, spinner, status)
- Validação sequencial (termos → CLIENT_ID → provedor backend)
- Pré-carregamento curto (categorias → home → EPG 24h)
- Fallback robusto com timeouts específicos
- Navegação final para Home (sempre)

### **🔄 Fluxo Sequencial (7 etapas):**

#### **4. verificarProvedorSalvo() - Validação Backend:**
```javascript
function verificarProvedorSalvo() {
    var provedor = localStorage.getItem('provedor');
    var usuario = localStorage.getItem('usuario');
    var senha = localStorage.getItem('senha');
    var dns = localStorage.getItem('dns');
    
    if (!provedor || !usuario || !senha || !dns) {
        console.log('[INDEX] Credenciais ausentes');
        updateStatus('Credenciais não encontradas', 25, 'warning');
        setTimeout(function() { window.nav('login'); }, 2000);
        return;
    }
    
    updateStatus('Validando provedor...', 30);
    
    // Testar conectividade backend Xtream
    var testUrl = window.buildApiUrl('get_live_categories');
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', testUrl, true);
    xhr.timeout = 10000;
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (Array.isArray(data) && data.length > 0) {
                    updateStatus('Provedor validado', 35, 'success');
                    setTimeout(function() {
                        iniciarPreCarregamentoCurto();
                    }, 500);
                } else {
                    throw new Error('Resposta inválida');
                }
            } catch (e) {
                console.error('[INDEX] Parse erro:', e);
                updateStatus('Dados inválidos do provedor', 0, 'error');
                setTimeout(function() { window.nav('login'); }, 2000);
            }
        } else {
            console.error('[INDEX] HTTP erro:', xhr.status);
            updateStatus('Provedor indisponível', 0, 'error');
            setTimeout(function() { window.nav('login'); }, 2000);
        }
    };
    
    xhr.onerror = function() {
        console.error('[INDEX] Erro de rede');
        updateStatus('Erro de conexão', 0, 'error');
        setTimeout(function() { window.nav('login'); }, 2000);
    };
    
    xhr.ontimeout = function() {
        console.error('[INDEX] Timeout validação');
        updateStatus('Timeout na validação', 0, 'error');
        setTimeout(function() { window.nav('login'); }, 2000);
    };
    
    xhr.send();
}
```

### **⚠️ Error Handling & Timeouts:**

#### **Timeouts Específicos:**
- **Validação backend:** 10s
- **Categorias (cada):** 15s
- **Home cache:** instantâneo
- **EPG:** 30s (crítico)

---

## 🏠 **Home.html**

### **📁 Arquivo:**
- `home.html` (Dashboard principal pós-autenticação)

### **🎯 Responsabilidades:**
- Dashboard principal da aplicação
- Conteúdo em destaque
- Navegação para canais/filmes/séries
- Sistema de branding ativo
- Resetador silencioso de atividade

### **🔄 Fluxo de Inicialização:**
```javascript
function inicializarHome() {
    console.log('[HOME] Inicializando dashboard...');
    
    // 1. Aplicar branding personalizado
    setTimeout(function() {
        window.loadBrandingLogo();
    }, 100);
    
    // 2. Verificar cache da home
    var homeCache = sessionStorage.getItem('ui_home_cache');
    if (homeCache) {
        var cacheData = JSON.parse(homeCache);
        if (cacheData.ready) {
            console.log('[HOME] Usando cache pré-carregado');
            renderHomeFast();
        }
    }
    
    // 3. Iniciar resetador silencioso
    iniciarResetadorSilencioso();
    
    // 4. Carregar conteúdo principal
    carregarConteudoPrincipal();
    
    // 5. Setup navegação por controle
    setupHomeNavigation();
}

function renderHomeFast() {
    // Renderização instantânea com dados cache
    var container = document.querySelector('#homeContainer');
    
    var html = '<div class="home-grid">';
    html += '<div class="home-section">';
    html += '<h2>📺 Canais ao Vivo</h2>';
    html += '<div class="nav-card lg-focusable" data-nav="canais">';
    html += '<img src="/assets/canais-icon.png" alt="Canais">';
    html += '<span>Assistir TV</span>';
    html += '</div></div>';
    
    html += '<div class="home-section">';
    html += '<h2>🎬 Filmes</h2>';
    html += '<div class="nav-card lg-focusable" data-nav="filmes">';
    html += '<img src="/assets/filmes-icon.png" alt="Filmes">';
    html += '<span>Catálogo de Filmes</span>';
    html += '</div></div>';
    
    html += '<div class="home-section">';
    html += '<h2>📚 Séries</h2>';
    html += '<div class="nav-card lg-focusable" data-nav="series">';
    html += '<img src="/assets/series-icon.png" alt="Séries">';
    html += '<span>Catálogo de Séries</span>';
    html += '</div></div>';
    html += '</div>';
    
    container.innerHTML = html;
}

function carregarConteudoPrincipal() {
    // Carregar estatísticas básicas
    carregarEstatisticasRapidas();
    
    // Carregar últimos assistidos (se houver)
    carregarUltimosAssistidos();
    
    // Setup focus management
    setupFocusManagement();
}

function setupHomeNavigation() {
    var navCards = document.querySelectorAll('.nav-card[data-nav]');
    
    for (var i = 0; i < navCards.length; i++) {
        navCards[i].addEventListener('click', function() {
            var destino = this.getAttribute('data-nav');
            window.nav(destino);
        });
        
        navCards[i].addEventListener('keydown', function(e) {
            if (e.keyCode === 13) { // Enter
                var destino = this.getAttribute('data-nav');
                window.nav(destino);
            }
        });
    }
}

function iniciarResetadorSilencioso() {
    var clientId = localStorage.getItem('client_id');
    var provedor = localStorage.getItem('provedor');
    
    if (!clientId || !provedor) return;
    
    // Função de reset silencioso
    function resetarAtividade() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'https://webnoma.space/api/verificar_sessao.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({
            client_id: clientId,
            provedor: provedor
        }));
        // Silencioso - sem tratamento de resposta
    }
    
    // Reset inicial
    resetarAtividade();
    
    // Repetir a cada 5 minutos
    setInterval(resetarAtividade, 5 * 60 * 1000);
}
```

### **🎨 Layout Home:**
```css
.home-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    padding: 40px;
}

.home-section {
    text-align: center;
}

.nav-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 40px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nav-card.lg-focused {
    transform: scale(1.1);
    box-shadow: 0 0 30px rgba(174, 255, 0, 0.6);
}

.nav-card img {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
}

.nav-card span {
    display: block;
    color: white;
    font-size: 24px;
    font-weight: bold;
}
```

---

## 📺 **Canais.html**

### **📁 Arquivo:**
- `canais.html` (Live TV streaming + EPG)

### **🎯 Responsabilidades:**
- Renderizar UI de canais (categorias já pré-carregadas)
- Fetch streams apenas na primeira vez (cache permanente)
- Player TV ao vivo + EPG

### **🔄 Fluxo Render-Only:**

#### **1. Inicialização (primeira vez fetch, depois render-only):**
```javascript
function inicializarCanais() {
    // Aplicar branding automaticamente
    setTimeout(function() {
        window.loadBrandingLogo();
    }, 100);
    
    // Categorias já estão prontas (Index pré-carregou)
    var categories = window.storage.temporary.get('categories_canais');
    
    if (!categories) {
        console.error('[CANAIS] Categorias não pré-carregadas');
        return;
    }
    
    // Verificar se streams já foram carregados
    var streamsCache = window.storage.temporary.get('cache_live_streams_all');
    
    if (streamsCache) {
        // Já existe - só renderizar
        renderChannelsList(categories, streamsCache);
    } else {
        // Primeira vez - fetch uma única vez
        fetchStreamsOnce(categories);
    }
}

function fetchStreamsOnce(categories) {
    updateStatus('Carregando canais...', 20);
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', window.buildApiUrl('get_live_streams'), true);
    xhr.timeout = 15000;
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var streams = JSON.parse(xhr.responseText);
                
                // Cache PERMANENTE (sem TTL)
                window.storage.temporary.set('cache_live_streams_all', streams);
                
                // Organizar por categoria
                organizeStreamsByCategory(streams, categories);
                
                updateStatus('Canais carregados', 100, 'success');
                renderChannelsList(categories, streams);
            } catch (e) {
                console.error('[CANAIS] Parse erro:', e);
                showError('Erro ao processar canais');
            }
        }
    };
    
    xhr.onerror = function() {
        console.error('[CANAIS] Erro de rede');
        showError('Erro ao carregar canais');
    };
    
    xhr.send();
}
```

### **🗂️ Storage (Cache Permanente):**
```javascript
// Sem TTL - persiste até reiniciar app
'categories_canais' → array (Index pré-carregou)
'cache_live_streams_all' → array completo
'cache_live_streams_<categoryId>' → array por categoria
```

---

## 🎬 **Filmes.html**

### **📁 Arquivo:**
- `filmes.html` (VOD movies)

### **🎯 Responsabilidades:**
- Renderizar catálogo de filmes (categorias prontas)
- Fetch filmes apenas primeira vez (cache permanente)
- Player VOD

### **🔄 Fluxo Render-Only:**

#### **1. Inicialização:**
```javascript
function inicializarFilmes() {
    // Aplicar branding automaticamente
    setTimeout(function() {
        window.loadBrandingLogo();
    }, 100);
    
    var categories = window.storage.temporary.get('categories_filmes');
    
    if (!categories) {
        console.error('[FILMES] Categorias não pré-carregadas');
        return;
    }
    
    var moviesCache = window.storage.temporary.get('nomatv_cache_vod_all');
    
    if (moviesCache) {
        renderMoviesList(categories, moviesCache);
    } else {
        fetchMoviesOnce(categories);
    }
}
```

### **🗂️ Storage (Cache Permanente):**
```javascript
'categories_filmes' → array (Index pré-carregou)
'nomatv_cache_vod_all' → array completo
'nomatv_cache_vod_<categoryId>' → array por categoria
```

---

## 📚 **Séries.html**

### **📁 Arquivo:**
- `series.html` (Series and episodes)

### **🎯 Responsabilidades:**
- Renderizar catálogo de séries (categorias prontas)
- Fetch séries apenas primeira vez (cache permanente)
- Progresso persistente (localStorage)

### **🔄 Fluxo Render-Only:**

#### **3. Progresso Persistente:**
```javascript
function saveSeriesProgress(seriesId, seasonNum, episodeNum, position) {
    var key = 'series_progress_' + seriesId;
    var progressData = {
        season: seasonNum,
        episode: episodeNum,
        position: position,
        timestamp: Date.now()
    };
    localStorage.setItem(key, JSON.stringify(progressData));
}

function getSeriesProgress(seriesId) {
    var key = 'series_progress_' + seriesId;
    var stored = localStorage.getItem(key);
    return stored ? JSON.parse(stored) : null;
}
```

### **🗂️ Storage (Cache Permanente + Progresso):**
```javascript
// Memory (sem TTL)
'categories_series' → array (Index pré-carregou)
'nomatv_cache_series_all' → array completo
'nomatv_cache_series_<categoryId>' → array por categoria

// localStorage (persistente)
'series_progress_<seriesId>' → {season, episode, position, timestamp}
```

---

## ⚙️ **Funções Globais (22 APIs)**

### **1. Navegação & Estado (6 funções):**
```javascript
// Navegação SPA principal
window.nav(destino: string): void
// Lista de sessões válidas
window.getSessoesValidas(): string[]
// Verificar se sessão existe
window.sessaoExiste(nome: string): boolean
// Estados disponíveis
window.appStates: {FULLSCREEN, PLAYER_VIEW, GRID_VIEW, HOME, MODAL}
// Estado atual
window.appState: string
// Mudar estado com logs
window.setAppState(state: string): void
```

### **2. Identidade & Autenticação (2 funções):**
```javascript
// UUID v4 persistente
window.getOrCreateClientId(): string
// Credenciais + isAuthenticated()
window.getAuthData(): {usuario, senha, dns, provedor, isAuthenticated()}
```

### **3. Storage Unificado (6 funções):**
```javascript
// localStorage wrapper
window.storage.persistent.set(key: string, data: any): boolean
window.storage.persistent.get(key: string): any | null

// Memory cache com TTL
window.storage.temporary.set(key: string, data: any, ttlMs?: number): void
window.storage.temporary.get(key: string): any | null

// IndexedDB wrapper (2 variantes)
window.idb.store(key, data, cb) // casca simplificada
window.idb.put(store, key, value, cb) // stores nominais
```

### **4. Cache de Categorias (2 funções):**
```javascript
// Ler: memória → sessionStorage → rede
window.getCategorias(tipo: 'canais'|'filmes'|'series', opts?, cb?): any
// Gravar: sessionStorage + memory
window.setCategorias(tipo, data, opts?): void
```

### **5. UI Global (5 funções):**
```javascript
// Loader padrão
window.ui.showLoading(containerId?: string, text?: string): void
window.ui.hideLoading(containerId?: string): void
// Loader da casca
window.updateLoadingProgress(text: string): void
// Status Index/Autenticando
window.updateStatus(message, details?, type?): void
window.updateProgress(percent, title?, subtitle?, type?): void
```

### **Chaves Padronizadas:**
```javascript
// SessionStorage
'categories_canais' → {data: [...], ts: timestamp}
'categories_filmes' → {data: [...], ts: timestamp}  
'categories_series' → {data: [...], ts: timestamp}
'ui_home_cache' → {ready: true, ts: timestamp}

// IndexedDB
('player_libs', 'current') → {data: [...], version: 'v1', ts: timestamp}
('epg', 'current') → {data: '<xml>', provedor: '...', ts: timestamp}
('live_streams', 'minimal') → [{stream_id, name, category_id}]

// Memory Cache
'categories_<tipo>' → array puro (TTL 6h)
'player_libs' → libs array (TTL 24h)
```

---

## 🎨 **CSS Otimizado Smart TV**

### **📺 Media Queries LG WebOS:**
```css
/* LG WebOS 1080p (Mais comum) */
@media screen and (width: 1920px) and (height: 1080px) {
    .lg-layout { display: flex; }
    .movie-card { width: 280px; height: 420px; }
    .nav-button { font-size: 18px; padding: 12px 24px; }
    .category-title { font-size: 24px; margin: 20px 0; }
    .channel-card { width: 160px; height: 90px; }
}

/* LG WebOS 4K */
@media screen and (width: 3840px) and (height: 2160px) {
    .lg-layout { display: flex; }
    .movie-card { width: 560px; height: 840px; }
    .nav-button { font-size: 36px; padding: 24px 48px; }
    .category-title { font-size: 48px; margin: 40px 0; }
    .channel-card { width: 320px; height: 180px; }
}
```

### **🎮 Navegação por Controle Remoto:**
```css
.lg-focusable {
    outline: none !important;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    border-radius: 8px;
    position: relative;
}

.lg-focusable.lg-focused {
    border-color: #AEFF00 !important;
    background-color: rgba(174, 255, 0, 0.1) !important;
    transform: scale(1.05);
    z-index: 999;
    box-shadow: 0 0 20px rgba(174, 255, 0, 0.5);
}
```

---

## 🛠️ **Templates ES5**

### **📋 Template Padrão de Função:**
```javascript
function lgFunctionTemplate(param1, param2, callback, errorCallback) {
    // 1. VALIDAÇÃO DE PARÂMETROS
    if (typeof param1 === 'undefined' || param1 === null) {
        var error = new Error('Parâmetro param1 obrigatório');
        console.error('[LG-FUNCTION] Parâmetro param1 obrigatório');
        if (errorCallback) errorCallback(error);
        return;
    }
    
    // 2. DECLARAÇÃO DE VARIÁVEIS LOCAIS (var obrigatório)
    var localVar1 = '';
    var localVar2 = {};
    var timer = null;
    var startTime = Date.now();
    
    // 3. LÓGICA DE TIMEOUT LG (8s padrão, EPG 30s)
    timer = setTimeout(function() {
        var elapsed = Date.now() - startTime;
        console.warn('[LG-FUNCTION] Timeout após ' + elapsed + 'ms');
        
        if (errorCallback) errorCallback(new Error('Timeout'));
    }, 8000);
    
    // 4. LÓGICA PRINCIPAL
    try {
        // XMLHttpRequest pattern para Smart TV
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.timeout = 7000; // Sempre menor que setTimeout
        
        xhr.onload = function() {
            clearTimeout(timer);
            
            if (xhr.status === 200) {
                try {
                    var result = JSON.parse(xhr.responseText);
                    var elapsed = Date.now() - startTime;
                    console.log('[LG-FUNCTION] Sucesso em ' + elapsed + 'ms');
                    
                    if (callback) callback(result);
                } catch (parseError) {
                    console.error('[LG-FUNCTION] Parse erro:', parseError);
                    if (errorCallback) errorCallback(parseError);
                }
            } else {
                var httpError = new Error('HTTP ' + xhr.status);
                console.error('[LG-FUNCTION] HTTP erro:', xhr.status);
                if (errorCallback) errorCallback(httpError);
            }
        };
        
        xhr.onerror = function() {
            clearTimeout(timer);
            console.error('[LG-FUNCTION] Erro de rede');
            if (errorCallback) errorCallback(new Error('Erro de rede'));
        };
        
        xhr.send();
        
    } catch (error) {
        clearTimeout(timer);
        console.error('[LG-FUNCTION] Erro geral:', error);
        if (errorCallback) errorCallback(error);
    }
}
```

---

## ⚡ **Performance TV**

### **🚀 Otimizações Obrigatórias:**

#### **Storage Performance Order:**
```javascript
// Ordem de prioridade por velocidade (ms):
// 1. Memory cache (0.1ms) - window.storage.temporary
// 2. sessionStorage (1-2ms) - session específica
// 3. localStorage (1-5ms) - persistente leve
// 4. IndexedDB (10-50ms) - dados pesados
// 5. Network (500-2000ms) - último recurso
```

#### **Memory Management Automático:**
```javascript
// Cleanup automático a cada 30s
var cleanupInterval = setInterval(function() {
    if (window.storage && window.storage.temporary && window.storage.temporary.cleanup) {
        window.storage.temporary.cleanup();
    }
    
    // Force GC se disponível (WebOS específico)
    if (window.gc) {
        try { window.gc(); } catch (e) {}
    }
    
    // Monitor memory usage
    if (performance && performance.memory) {
        var used = performance.memory.usedJSHeapSize / 1024 / 1024;
        var limit = performance.memory.jsHeapSizeLimit / 1024 / 1024;
        
        if ((used / limit) > 0.8) {
            console.warn('[LG-MEMORY] Alto uso de memória: ' + used.toFixed(1) + 'MB');
            if (window.storage.temporary.clear) {
                window.storage.temporary.clear();
            }
        }
    }
}, 30000);
```

---

# 🖥️ SEÇÃO 2: FRONTEND PAINÉIS WEB

## 👑 **admin.html (master='admin')**

### **📁 Arquivo:**
- `admin.html` (Painel supremo com acesso global)

### **🎯 Permissões & Responsabilidades:**
- **Acesso global** a todos os dados do sistema
- **Criar/editar/deletar** qualquer revendedor (Master ou Sub)
- **Gerenciar todos** os provedores
- **Estatísticas globais** e relatórios completos
- **Sistema financeiro** irrestrito
- **Branding:** Logo NomaApp (padrão)

### **🔄 Funcionalidades Específicas:**

#### **1. Dashboard Global:**
```javascript
function carregarDashboardAdmin() {
    // Stats globais sem filtros
    window.api.getStats().then(function(response) {
        if (response.success) {
            renderStatsGlobais(response.data);
        }
    });
    
    // Últimas atividades do sistema
    carregarAtividadesRecentes();
    
    // Alertas e notificações
    carregarAlertas();
}

function renderStatsGlobais(stats) {
    document.getElementById('totalRevendedores').textContent = stats.totalRevendedores;
    document.getElementById('totalAtivos').textContent = stats.totalAtivos;
    document.getElementById('totalProvedores').textContent = stats.totalProvedores;
    document.getElementById('receitaTotal').textContent = 'R$ ' + stats.receitaTotal;
}
```

#### **2. Gestão Irrestrita:**
```javascript
// Criar qualquer tipo de revendedor
function criarRevendedor(tipo) {
    var formData = {
        nome: document.getElementById('nome').value,
        usuario: document.getElementById('usuario').value,
        senha: document.getElementById('senha').value,
        master: tipo, // 'sim' ou 'nao'
        parent_id: document.getElementById('parentSelect').value,
        ativo: true
    };
    
    window.api.createRevendedor(formData).then(function(response) {
        if (response.success) {
            showAlert('Revendedor criado com sucesso!', 'success');
            recarregarTabela();
        }
    });
}

// Filtrar por qualquer revendedor
function filtrarPorRevendedor(revendedorId) {
    var filtros = {
        revendedor_id: revendedorId,
        page: 1,
        limit: 25
    };
    
    window.api.getRevendedores(filtros).then(function(response) {
        renderTabelaRevendedores(response.data);
    });
}
```

### **🎨 Layout Admin:**
```css
.admin-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 40px;
}

.admin-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
}

.admin-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}

.admin-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
}
```

---

## 🏪 **revendedor.html (master='sim')**

### **📁 Arquivo:**
- `revendedor.html` (Painel Master com rede descendente)

### **🎯 Permissões & Responsabilidades:**
- **Ver toda sua rede** descendente (busca recursiva)
- **Criar apenas sub-revendedores** (master='nao')
- **Gerenciar provedores** da sua rede
- **Branding personalizado** aplicado
- **Estatísticas da rede** própria

### **🔄 Funcionalidades Específicas:**

#### **1. Dashboard da Rede:**
```javascript
function carregarDashboardRede() {
    // Stats automáticas da rede (backend filtra)
    window.api.getStats().then(function(response) {
        if (response.success) {
            renderStatsRede(response.data);
        }
    });
    
    // Mapa da rede descendente
    carregarMapaRede();
}

function carregarMapaRede() {
    var revendedorId = localStorage.getItem('id_revendedor');
    
    window.api.getRedeRevendedor(revendedorId).then(function(response) {
        if (response.success) {
            renderMapaRede(response.data);
        }
    });
}

function renderMapaRede(dados) {
    var container = document.getElementById('mapaRede');
    var html = '<div class="rede-metricas">';
    html += '<div class="metrica"><span>Ativos Total:</span> ' + dados.metricas.ativos_total + '</div>';
    html += '<div class="metrica"><span>Sub-Revendedores:</span> ' + dados.metricas.total_revendedores + '</div>';
    html += '<div class="metrica"><span>Diretos:</span> ' + dados.metricas.revendedores_diretos + '</div>';
    html += '<div class="metrica"><span>Indiretos:</span> ' + dados.metricas.revendedores_indiretos + '</div>';
    html += '</div>';
    
    container.innerHTML = html;
}
```

#### **2. Criação Restrita:**
```javascript
// Só pode criar sub-revendedores
function criarSubRevendedor() {
    var loggedId = localStorage.getItem('id_revendedor');
    
    var formData = {
        nome: document.getElementById('nome').value,
        usuario: document.getElementById('usuario').value,
        senha: document.getElementById('senha').value,
        master: 'nao', // SEMPRE 'nao'
        parent_id: loggedId, // SEMPRE ele mesmo
        ativo: true
    };
    
    window.api.createRevendedor(formData).then(function(response) {
        if (response.success) {
            showAlert('Sub-revendedor criado com sucesso!', 'success');
            recarregarRede();
        }
    });
}
```

#### **3. Branding Próprio:**
```javascript
function aplicarBrandingProprio() {
    var revendedorId = localStorage.getItem('id_revendedor');
    var logoImg = document.querySelector('#logoImg');
    
    if (logoImg && revendedorId) {
        logoImg.src = '/api/logo_proxy.php?id=' + revendedorId;
        logoImg.onerror = function() {
            logoImg.src = '/logos/nomaapp.png';
        };
    }
}

// Aplicar no carregamento
document.addEventListener('DOMContentLoaded', function() {
    aplicarBrandingProprio();
});
```

---

## 👤 **sub_revendedor.html (master='nao')**

### **📁 Arquivo:**
- `sub_revendedor.html` (Painel restrito pessoal)

### **🎯 Permissões & Responsabilidades:**
- **Ver apenas dados próprios**
- **NÃO pode criar** outros revendedores
- **Gerenciar apenas** seus próprios ativos
- **Branding herdado** do master (pai)
- **Dashboard pessoal** limitado

### **🔄 Funcionalidades Específicas:**

#### **1. Dashboard Pessoal:**
```javascript
function carregarDashboardPessoal() {
    // Stats apenas próprias (backend filtra automaticamente)
    window.api.getStats().then(function(response) {
        if (response.success) {
            renderStatsPessoais(response.data);
        }
    });
    
    // Próprios ativos apenas
    carregarMeusAtivos();
}

function renderStatsPessoais(stats) {
    // Interface simplificada
    document.getElementById('meusAtivos').textContent = stats.totalAtivos;
    document.getElementById('meuPlano').textContent = stats.planoAtual;
    document.getElementById('proximoVencimento').textContent = stats.proximoVencimento;
}

// SEM função de criar revendedores
// criarRevendedor() - FUNÇÃO NÃO EXISTE neste painel
```

#### **2. Branding Herdado:**
```javascript
function aplicarBrandingHerdado() {
    var parentId = localStorage.getItem('parent_id');
    var logoImg = document.querySelector('#logoImg');
    
    if (logoImg && parentId) {
        // Usa logo do pai (master)
        logoImg.src = '/api/logo_proxy.php?id=' + parentId;
        logoImg.onerror = function() {
            logoImg.src = '/logos/nomaapp.png';
        };
    }
}
```

#### **3. Interface Limitada:**
```css
/* Esconder botões de criação */
.create-revendedor-btn {
    display: none !important;
}

/* Layout simplificado */
.sub-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.sub-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 20px;
    border-radius: 8px;
}
```

---

## 🌐 **api.js - Ponte Frontend-Backend**

### **📡 Comunicação Centralizada:**
```javascript
// api.js - Abstração de todas as chamadas backend
class NomaAPI {
    constructor() {
        this.baseURL = 'https://webnoma.space/api';
        this.headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    }

    // Wrapper para todas as requisições
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}/${endpoint}`;
        const config = {
            headers: this.headers,
            credentials: 'include', // Para sessões PHP
            ...options
        };

        try {
            const response = await fetch(url, config);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('[API] Erro:', error);
            throw error;
        }
    }

    // Auth endpoints
    async login(usuario, senha) {
        return this.request('auth.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'login', usuario, senha })
        });
    }

    async logout() {
        return this.request('auth.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'logout' })
        });
    }

    // CRUD Revendedores
    async getRevendedores(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`revendedores.php?${params}`);
    }

    async createRevendedor(data) {
        return this.request('revendedores.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updateRevendedor(id, data) {
        return this.request(`revendedores.php?id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async deleteRevendedor(id) {
        return this.request(`revendedores.php?id=${id}`, {
            method: 'DELETE'
        });
    }

    // CRUD Provedores
    async getProvedores(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`provedores.php?${params}`);
    }

    async testProvedor(dns, usuario, senha) {
        return this.request('provedores.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'testar_conexao',
                dns, usuario, senha
            })
        });
    }

    // CRUD Client IDs (Ativos)
    async getClientIds(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`client_ids.php?${params}`);
    }

    // Stats Dashboard
    async getStats() {
        return this.request('stats.php');
    }

    // Rede Revendedor
    async getRedeRevendedor(id) {
        return this.request(`rede_revendedor.php?id=${id}`);
    }

    // Financeiro
    async getFinanceiro(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`financeiro.php?${params}`);
    }

    async createFatura(data) {
        return this.request('financeiro.php', {
            method: 'POST',
            body: JSON.stringify({ action: 'criar_fatura', ...data })
        });
    }
}

// Instância global
window.api = new NomaAPI();
```

---

## 🎭 **Sistema Branding Painéis**

### **🎨 Estratégia Hierárquica:**

#### **admin.html - Logo Padrão:**
```javascript
function setBrandingAdmin() {
    var logoImg = document.querySelector('#logoImg, .logo-img');
    if (logoImg) {
        logoImg.src = '/logos/nomaapp.png';
        logoImg.alt = 'NomaApp Admin';
    }
}
```

#### **revendedor.html - Logo Própria:**
```javascript
function setBrandingRevendedor() {
    var revendedorId = localStorage.getItem('id_revendedor');
    var logoImg = document.querySelector('#logoImg, .logo-img');
    
    if (logoImg && revendedorId) {
        // Cache primeiro
        var cacheKey = 'branding_logo_' + revendedorId;
        var cached = sessionStorage.getItem(cacheKey);
        
        if (cached) {
            var cacheData = JSON.parse(cached);
            if (cacheData.expires > Date.now()) {
                logoImg.src = cacheData.url;
                return;
            }
        }
        
        // Buscar logo
        logoImg.src = '/api/logo_proxy.php?id=' + revendedorId;
        logoImg.onload = function() {
            // Cache por 1h
            sessionStorage.setItem(cacheKey, JSON.stringify({
                url: logoImg.src,
                expires: Date.now() + (60 * 60 * 1000)
            }));
        };
        logoImg.onerror = function() {
            logoImg.src = '/logos/nomaapp.png';
        };
    }
}
```

#### **sub_revendedor.html - Logo Herdada:**
```javascript
function setBrandingSub() {
    var parentId = localStorage.getItem('parent_id');
    var logoImg = document.querySelector('#logoImg, .logo-img');
    
    if (logoImg && parentId) {
        logoImg.src = '/api/logo_proxy.php?id=' + parentId;
        logoImg.onerror = function() {
            logoImg.src = '/logos/nomaapp.png';
        };
    }
}
```

---

## 🔍 **Filtros & Paginação**

### **🔍 Sistema de Filtros Avançados:**
```javascript
// Filtros universais nos painéis
class FiltrosAvancados {
    constructor(endpoint) {
        this.endpoint = endpoint;
        this.filtros = {};
        this.paginacao = { page: 1, limit: 25 };
    }
    
    setFiltro(key, value) {
        if (value && value.trim() !== '') {
            this.filtros[key] = value;
        } else {
            delete this.filtros[key];
        }
        this.paginacao.page = 1; // Reset para primeira página
    }
    
    setPaginacao(page, limit = 25) {
        this.paginacao = { page: Math.max(1, page), limit: Math.min(100, limit) };
    }
    
    async aplicar() {
        const params = { ...this.filtros, ...this.paginacao };
        return await window.api.request(`${this.endpoint}?${new URLSearchParams(params)}`);
    }
    
    reset() {
        this.filtros = {};
        this.paginacao = { page: 1, limit: 25 };
    }
}

// Uso nos painéis
const filtrosRevendedores = new FiltrosAvancados('revendedores.php');

// Setup de filtros
function setupFiltros() {
    // Search input
    document.getElementById('searchInput').addEventListener('input', function() {
        filtrosRevendedores.setFiltro('search', this.value);
        aplicarFiltrosComDelay();
    });
    
    // Status select
    document.getElementById('statusSelect').addEventListener('change', function() {
        filtrosRevendedores.setFiltro('status', this.value);
        aplicarFiltros();
    });
    
    // Período select
    document.getElementById('periodoSelect').addEventListener('change', function() {
        filtrosRevendedores.setFiltro('periodo', this.value);
        aplicarFiltros();
    });
}

// Delay para search input
var searchTimeout;
function aplicarFiltrosComDelay() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(aplicarFiltros, 500);
}

async function aplicarFiltros() {
    try {
        showLoading('Aplicando filtros...');
        const response = await filtrosRevendedores.aplicar();
        
        if (response.success) {
            renderTabela(response.data);
            updatePaginacao(response.extraData.pagination);
        }
    } catch (error) {
        showAlert('Erro ao aplicar filtros', 'error');
    } finally {
        hideLoading();
    }
}
```

### **📄 Sistema de Paginação:**
```javascript
function updatePaginacao(pagination) {
    var container = document.getElementById('paginacao');
    var html = '';
    
    // Botão anterior
    if (pagination.page > 1) {
        html += '<button onclick="irParaPagina(' + (pagination.page - 1) + ')">‹ Anterior</button>';
    }
    
    // Números das páginas
    var startPage = Math.max(1, pagination.page - 2);
    var endPage = Math.min(pagination.totalPages, pagination.page + 2);
    
    for (var i = startPage; i <= endPage; i++) {
        var activeClass = i === pagination.page ? ' active' : '';
        html += '<button class="page-btn' + activeClass + '" onclick="irParaPagina(' + i + ')">' + i + '</button>';
    }
    
    // Botão próximo
    if (pagination.page < pagination.totalPages) {
        html += '<button onclick="irParaPagina(' + (pagination.page + 1) + ')">Próximo ›</button>';
    }
    
    // Info
    html += '<span class="pagination-info">Página ' + pagination.page + ' de ' + pagination.totalPages + 
           ' (' + pagination.total + ' registros)</span>';
    
    container.innerHTML = html;
}

function irParaPagina(page) {
    filtrosRevendedores.setPaginacao(page);
    aplicarFiltros();
}
```

---

# 🌐 SEÇÃO 3: BACKEND COMPLETO

## 🗃️ **Estrutura de Tabelas**

### **📊 Tabela Principal: revendedores**
```sql
CREATE TABLE revendedores (
    id_revendedor INTEGER PRIMARY KEY,      -- Chave mestra (8 dígitos)
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    master VARCHAR(10) NOT NULL,            -- 'admin', 'sim', 'nao'
    parent_id INTEGER,                      -- Hierarquia recursiva
    ativo BOOLEAN DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES revendedores(id_revendedor)
);
```

### **📊 Tabela: provedores**
```sql
CREATE TABLE provedores (
    id_provedor INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) UNIQUE NOT NULL,
    dns VARCHAR(255) NOT NULL,
    id_revendedor INTEGER NOT NULL,
    ativo BOOLEAN DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor)
);
```

### **📊 Tabela: client_ids (Ativos)**
```sql
CREATE TABLE client_ids (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id VARCHAR(36) UNIQUE NOT NULL,
    provedor_id INTEGER NOT NULL,
    id_revendedor INTEGER NOT NULL,
    usuario VARCHAR(100),
    ultima_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provedor_id) REFERENCES provedores(id_provedor),
    FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor)
);
```

### **📊 Tabelas Financeiras:**
```sql
-- Planos disponíveis
CREATE TABLE planos (
    id_plano INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    duracao_dias INTEGER NOT NULL,
    ativo BOOLEAN DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Faturas geradas
CREATE TABLE faturas (
    id_fatura INTEGER PRIMARY KEY AUTOINCREMENT,
    id_revendedor INTEGER NOT NULL,
    id_plano INTEGER NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    vencimento DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'pendente', -- pendente, pago, vencido
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor),
    FOREIGN KEY (id_plano) REFERENCES planos(id_plano)
);

-- Pagamentos registrados
CREATE TABLE pagamentos (
    id_pagamento INTEGER PRIMARY KEY AUTOINCREMENT,
    id_fatura INTEGER NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    metodo_pagamento VARCHAR(50), -- pix, boleto, cartao
    comprovante TEXT,
    pago_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_fatura) REFERENCES faturas(id_fatura)
);
```

---

## 🔐 **Sistema de Autenticação**

### **🔒 Padrão Obrigatório (5 linhas):**
```php
// ✅ CÓDIGO OBRIGATÓRIO em TODOS os endpoints
session_start();
if (empty($_SESSION['id_revendedor']) || empty($_SESSION['master'])) {
    http_response_code(401);
    exit('{"success":false,"message":"Usuário não autenticado"}');
}
$loggedInRevendedorId = $_SESSION['id_revendedor'];
$loggedInUserType = $_SESSION['master'];
```

### **📋 Variáveis de Sessão:**
```php
$_SESSION['id_revendedor'] = "12345678";  // ID único (8 dígitos)
$_SESSION['master'] = "admin";            // 'admin', 'sim', 'nao'
$_SESSION['usuario'] = "admin";           // Username
$_SESSION['nome'] = "João Silva";         // Nome completo
```

### **🔄 Resposta Padronizada:**
```php
function standardResponse(bool $success, $data = null, $message = null, $extraData = null): void
{
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'extraData' => $extraData
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
```

---

## 📍 **Endpoints Completos**

### **🔐 auth.php - Autenticação:**
```php
<?php
/**
 * ENDPOINT AUTH - NomaTV API v4.5
 * RESPONSABILIDADES: Login/Logout dos painéis
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/database_sqlite.php';

function standardResponse(bool $success, $data = null, $message = null): void
{
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';

if ($action === 'login') {
    $usuario = $input['usuario'] ?? '';
    $senha = $input['senha'] ?? '';
    
    if (empty($usuario) || empty($senha)) {
        standardResponse(false, null, 'Usuário e senha são obrigatórios.');
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM revendedores WHERE usuario = ? AND ativo = 1");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['id_revendedor'] = $user['id_revendedor'];
            $_SESSION['master'] = $user['master'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['nome'] = $user['nome'];
            
            standardResponse(true, [
                'master' => $user['master'],
                'nome' => $user['nome'],
                'redirect' => getRedirectUrl($user['master'])
            ], 'Login realizado com sucesso.');
        } else {
            standardResponse(false, null, 'Credenciais inválidas.');
        }
    } catch (Exception $e) {
        error_log("NomaTV Auth erro: " . $e->getMessage());
        standardResponse(false, null, 'Erro interno.');
    }
} elseif ($action === 'logout') {
    session_destroy();
    standardResponse(true, null, 'Logout realizado.');
} else {
    standardResponse(false, null, 'Ação inválida.');
}

function getRedirectUrl($master) {
    switch ($master) {
        case 'admin': return 'admin.html';
        case 'sim': return 'revendedor.html';
        case 'nao': return 'sub_revendedor.html';
        default: return 'index.html';
    }
}
?>
```

### **🚪 verificar_provedor.php - Porteiro:**
```php
<?php
/**
 * ENDPOINT PORTEIRO - NomaTV API v4.5
 * RESPONSABILIDADE: Decidir roteamento do app
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database_sqlite.php';

function standardResponse(bool $success, $data = null, $message = null): void
{
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $provedor = $input['provedor'] ?? '';
    
    if (empty($provedor)) {
        standardResponse(false, null, 'Nome do provedor é obrigatório.');
    }
    
    try {
        $stmt = $db->prepare("
            SELECT p.dns, p.id_revendedor
            FROM provedores p
            JOIN revendedores r ON p.id_revendedor = r.id_revendedor  
            WHERE LOWER(p.nome) = LOWER(?) AND p.ativo = 1 AND r.ativo = 1
        ");
        $stmt->execute([$provedor]);
        $result = $stmt->fetch();
        
        if ($result) {
            standardResponse(true, [
                'dns' => $result['dns'],
                'revendedor_id' => $result['id_revendedor']
            ], 'Provedor encontrado.');
        } else {
            standardResponse(false, null, 'Provedor inativo ou não encontrado.');
        }
    } catch (Exception $e) {
        error_log("NomaTV Porteiro erro: " . $e->getMessage());
        standardResponse(false, null, 'Erro interno.');
    }
} else {
    http_response_code(405);
    standardResponse(false, null, 'Método não permitido.');
}
?>
```

### **🔗 validar_login.php - Vinculador:**
```php
<?php
/**
 * ENDPOINT VINCULADOR - NomaTV API v4.5
 * RESPONSABILIDADE: Validar + vincular client_id
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database_sqlite.php';

function standardResponse(bool $success, $data = null, $message = null): void
{
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $provedor = $input['provedor'] ?? '';
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $clientId = $input['client_id'] ?? '';
    
    if (empty($provedor) || empty($username) || empty($password) || empty($clientId)) {
        standardResponse(false, null, 'Todos os campos são obrigatórios.');
    }
    
    try {
        // Validar provedor
        $stmt = $db->prepare("
            SELECT p.id_provedor, p.nome, p.dns, p.id_revendedor 
            FROM provedores p 
            JOIN revendedores r ON p.id_revendedor = r.id_revendedor
            WHERE LOWER(p.nome) = LOWER(?) AND p.ativo = 1 AND r.ativo = 1
        ");
        $stmt->execute([$provedor]);
        $provedorData = $stmt->fetch();
        
        if (!$provedorData) {
            standardResponse(false, null, 'Provedor não encontrado ou inativo.');
        }
        
        // Verificar se client_id já existe
        $stmt = $db->prepare("SELECT id FROM client_ids WHERE client_id = ?");
        $stmt->execute([$clientId]);
        $existingClient = $stmt->fetch();
        
        if ($existingClient) {
            // Atualizar vinculação existente
            $stmt = $db->prepare("
                UPDATE client_ids 
                SET provedor_id = ?, id_revendedor = ?, usuario = ?, ultima_atividade = CURRENT_TIMESTAMP, ativo = 1
                WHERE client_id = ?
            ");
            $stmt->execute([$provedorData['id_provedor'], $provedorData['id_revendedor'], $username, $clientId]);
        } else {
            // Criar nova vinculação
            $stmt = $db->prepare("
                INSERT INTO client_ids (client_id, provedor_id, id_revendedor, usuario, ativo) 
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->execute([$clientId, $provedorData['id_provedor'], $provedorData['id_revendedor'], $username]);
        }
        
        // Retornar 5 variáveis para o app
        standardResponse(true, [
            'provedor' => $provedorData['nome'],
            'username' => $username,
            'password' => $password,
            'dns' => $provedorData['dns'],
            'revendedor_id' => $provedorData['id_revendedor']
        ], 'Vinculação realizada com sucesso.');
        
    } catch (Exception $e) {
        error_log("NomaTV Vinculador erro: " . $e->getMessage());
        standardResponse(false, null, 'Erro interno.');
    }
} else {
    http_response_code(405);
    standardResponse(false, null, 'Método não permitido.');
}
?>
```

### **🔄 verificar_sessao.php - Resetador:**
```php
<?php
/**
 * ENDPOINT RESETADOR - NomaTV API v4.5
 * RESPONSABILIDADE: Reset silencioso de atividade
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(); // Silencioso
}

require_once __DIR__ . '/config/database_sqlite.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $clientId = $input['client_id'] ?? '';
    $provedor = $input['provedor'] ?? '';
    
    if (empty($clientId)) {
        exit(); // Silencioso - sem resposta
    }
    
    try {
        if (!empty($provedor)) {
            // Cenário 1: Provedor + client_id (reset normal)
            $stmt = $db->prepare("
                SELECT c.id 
                FROM client_ids c
                JOIN provedores p ON c.provedor_id = p.id_provedor
                WHERE c.client_id = ? AND LOWER(p.nome) = LOWER(?)
            ");
            $stmt->execute([$clientId, $provedor]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Reset cronômetro
                $stmt = $db->prepare("
                    UPDATE client_ids 
                    SET ultima_atividade = CURRENT_TIMESTAMP, ativo = 1
                    WHERE client_id = ?
                ");
                $stmt->execute([$clientId]);
            }
        } else {
            // Cenário 2: Só client_id (primeiro acesso) - não faz nada
        }
        
    } catch (Exception $e) {
        error_log("NomaTV Resetador erro: " . $e->getMessage());
    }
}

// Sempre silencioso - sem resposta JSON
exit();
?>
```

---

## 🌳 **Sistema Hierárquico**

### **🔍 Busca Recursiva (função padrão):**
```php
function buscarRedeCompleta(PDO $db, string $idRevendedor): array
{
    $idsParaBuscar = [$idRevendedor];
    $todosDescendentes = [];
    $indice = 0;
    
    while ($indice < count($idsParaBuscar)) {
        $idAtual = $idsParaBuscar[$indice];
        
        $stmt = $db->prepare("
            SELECT id_revendedor
            FROM revendedores 
            WHERE parent_id = ? AND ativo = 1
        ");
        $stmt->execute([$idAtual]);
        $filhos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($filhos)) {
            $todosDescendentes = array_merge($todosDescendentes, $filhos);
            $idsParaBuscar = array_merge($idsParaBuscar, $filhos);
        }
        
        $indice++;
    }
    
    return array_diff($todosDescendentes, [$idRevendedor]);
}
```

### **🎯 Filtros Automáticos por Hierarquia:**
```php
// ✅ LÓGICA HIERÁRQUICA PADRÃO (todos os endpoints)
function aplicarFiltrosHierarquicos(string $loggedInUserType, string $loggedInRevendedorId, PDO $db): array
{
    $whereConditions = [];
    $queryParams = [];
    
    if ($loggedInUserType === "admin") {
        // Admin vê todos os dados - pode filtrar por revendedor específico
        // Sem condições WHERE por padrão
        
    } elseif ($loggedInUserType === "sim") {
        // Revendedor Master vê toda sua rede descendente
        $redeCompleta = buscarRedeCompleta($db, $loggedInRevendedorId);
        $idsPermitidos = array_merge([$loggedInRevendedorId], $redeCompleta);
        
        $placeholders = implode(',', array_fill(0, count($idsPermitidos), '?'));
        $whereConditions[] = "id_revendedor IN ($placeholders)";
        $queryParams = array_merge($queryParams, $idsPermitidos);
        
    } else {
        // Sub-revendedor vê apenas seus próprios dados
        $whereConditions[] = "id_revendedor = ?";
        $queryParams[] = $loggedInRevendedorId;
    }
    
    return [
        'conditions' => $whereConditions,
        'params' => $queryParams
    ];
}
```

---

## 💰 **Sistema Financeiro**

### **📍 financeiro.php - Endpoint Completo:**
```php
<?php
/**
 * ENDPOINT FINANCEIRO - NomaTV API v4.5
 * RESPONSABILIDADES: CRUD faturas/pagamentos + hierarquia
 */

// [Headers CORS + Autenticação padrão]

function listarFinanceiro(PDO $db, array $params, string $loggedInRevendedorId, string $loggedInUserType): void
{
    $filtros = aplicarFiltrosHierarquicos($loggedInUserType, $loggedInRevendedorId, $db);
    $whereConditions = array_merge(["f.ativo = 1"], $filtros['conditions']);
    $queryParams = $filtros['params'];
    
    // Filtros adicionais
    if (!empty($params['status'])) {
        $whereConditions[] = "f.status = ?";
        $queryParams[] = $params['status'];
    }
    
    if (!empty($params['periodo'])) {
        $whereConditions[] = "f.criado_em >= DATE('now', '-' || ? || ' months')";
        $queryParams[] = $params['periodo'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $sql = "
        SELECT f.*, r.nome as revendedor_nome, p.nome as plano_nome,
               COALESCE(SUM(pg.valor_pago), 0) as valor_pago
        FROM faturas f
        JOIN revendedores r ON f.id_revendedor = r.id_revendedor  
        JOIN planos p ON f.id_plano = p.id_plano
        LEFT JOIN pagamentos pg ON f.id_fatura = pg.id_fatura
        WHERE $whereClause
        GROUP BY f.id_fatura
        ORDER BY f.criado_em DESC
        LIMIT ? OFFSET ?
    ";
    
    $limit = min((int)($params['limit'] ?? 25), 100);
    $page = max((int)($params['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;
    
    $queryParams[] = $limit;
    $queryParams[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($queryParams);
    $faturas = $stmt->fetchAll();
    
    // Contar total
    $countSql = "SELECT COUNT(*) FROM faturas f WHERE " . $whereClause;
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array_slice($queryParams, 0, -2));
    $total = $countStmt->fetchColumn();
    
    standardResponse(true, $faturas, 'Dados financeiros listados.', [
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit)
        ]
    ]);
}

function criarFatura(PDO $db, string $loggedInRevendedorId, string $loggedInUserType, array $input): void
{
    // Validações
    if (empty($input['id_revendedor']) || empty($input['id_plano'])) {
        standardResponse(false, null, 'Revendedor e plano são obrigatórios.');
        return;
    }
    
    // Verificar permissão hierárquica
    if ($loggedInUserType !== 'admin') {
        if ($loggedInUserType === 'sim') {
            $redeCompleta = buscarRedeCompleta($db, $loggedInRevendedorId);
            $idsPermitidos = array_merge([$loggedInRevendedorId], $redeCompleta);
            if (!in_array($input['id_revendedor'], $idsPermitidos)) {
                standardResponse(false, null, 'Sem permissão para criar fatura para este revendedor.');
                return;
            }
        } else {
            if ($input['id_revendedor'] !== $loggedInRevendedorId) {
                standardResponse(false, null, 'Pode criar fatura apenas para si mesmo.');
                return;
            }
        }
    }
    
    // Buscar plano
    $stmt = $db->prepare("SELECT * FROM planos WHERE id_plano = ? AND ativo = 1");
    $stmt->execute([$input['id_plano']]);
    $plano = $stmt->fetch();
    
    if (!$plano) {
        standardResponse(false, null, 'Plano não encontrado.');
        return;
    }
    
    // Calcular vencimento
    $vencimento = date('Y-m-d', strtotime('+' . $plano['duracao_dias'] . ' days'));
    
    // Inserir fatura
    $stmt = $db->prepare("
        INSERT INTO faturas (id_revendedor, id_plano, valor, vencimento)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $input['id_revendedor'],
        $plano['id_plano'],
        $plano['preco'],
        $vencimento
    ]);
    
    $faturaId = $db->lastInsertId();
    
    standardResponse(true, ['id_fatura' => $faturaId], 'Fatura criada com sucesso.');
}
?>
```

---

## 📊 **Stats & Dashboard**

### **📈 stats.php - Métricas Completas:**
```php
<?php
/**
 * ENDPOINT STATS - NomaTV API v4.5
 * RESPONSABILIDADE: Dashboard com filtros hierárquicos
 */

// [Headers CORS + Autenticação padrão]

function getStatsCompletas(PDO $db, string $loggedInRevendedorId, string $loggedInUserType): void
{
    $filtros = aplicarFiltrosHierarquicos($loggedInUserType, $loggedInRevendedorId, $db);
    $whereClause = "";
    $params = $filtros['params'];
    
    if (!empty($filtros['conditions'])) {
        $whereClause = "WHERE " . implode(' AND ', $filtros['conditions']);
    }
    
    $stats = [];
    
    // Stats Revendedores
    $sql = "SELECT COUNT(*) as total, 
                   SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos
            FROM revendedores $whereClause";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats['revendedores'] = $stmt->fetch();
    
    // Stats Client IDs (Ativos)
    $sql = "SELECT COUNT(*) as total,
                   SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
                   SUM(CASE WHEN ultima_atividade >= datetime('now', '-24 hours') THEN 1 ELSE 0 END) as ativos_24h
            FROM client_ids $whereClause";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats['client_ids'] = $stmt->fetch();
    
    // Stats Provedores
    $sql = "SELECT COUNT(*) as total,
                   SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos
            FROM provedores $whereClause";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats['provedores'] = $stmt->fetch();
    
    // Stats Financeiras
    $sql = "SELECT COUNT(*) as total_faturas,
                   SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                   SUM(CASE WHEN status = 'vencido' THEN 1 ELSE 0 END) as vencidas,
                   SUM(valor) as valor_total
            FROM faturas $whereClause";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats['financeiro'] = $stmt->fetch();
    
    // Adicionar métricas extras
    $stats['periodo'] = [
        'inicio' => date('Y-m-01'),
        'fim' => date('Y-m-t')
    ];
    
    standardResponse(true, $stats, 'Estatísticas carregadas com sucesso.');
}
?>
```

---

## 🗄️ **MySQL Produção**

### **⚙️ Configuração Dual (SQLite/MySQL):**

#### **database_config.php:**
```php
<?php
/**
 * CONFIGURAÇÃO DUAL DE BANCO - NomaTV API v4.5
 * SQLite: Desenvolvimento
 * MySQL: Produção
 */

// Detectar ambiente
$isProduction = isset($_SERVER['HTTP_HOST']) && 
                strpos($_SERVER['HTTP_HOST'], 'webnoma.space') !== false;

if ($isProduction) {
    // MYSQL PRODUÇÃO
    $config = [
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'nomatv_prod',
        'username' => $_ENV['DB_USERNAME'] ?? 'nomatv_user',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    ];
    
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    } catch (PDOException $e) {
        error_log("NomaTV MySQL Connection Error: " . $e->getMessage());
        http_response_code(500);
        exit(json_encode(['success' => false, 'error' => 'Database connection failed']));
    }
    
} else {
    // SQLITE DESENVOLVIMENTO
    $dbFile = __DIR__ . '/../db.db';
    
    try {
        $db = new PDO("sqlite:$dbFile");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON;');
    } catch (PDOException $e) {
        error_log("NomaTV SQLite Connection Error: " . $e->getMessage());
        http_response_code(500);
        exit(json_encode(['success' => false, 'error' => 'Database connection failed']));
    }
}
?>
```

#### **migration_mysql.sql:**
```sql
-- MIGRAÇÃO SQLITE → MYSQL
-- NomaTV API v4.5

CREATE DATABASE IF NOT EXISTS nomatv_prod 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE nomatv_prod;

-- Tabela principal
CREATE TABLE revendedores (
    id_revendedor BIGINT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    master VARCHAR(10) NOT NULL,
    parent_id BIGINT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parent_id (parent_id),
    INDEX idx_master (master),
    INDEX idx_ativo (ativo),
    FOREIGN KEY (parent_id) REFERENCES revendedores(id_revendedor) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Provedores
CREATE TABLE provedores (
    id_provedor INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) UNIQUE NOT NULL,
    dns VARCHAR(255) NOT NULL,
    id_revendedor BIGINT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_revendedor (id_revendedor),
    INDEX idx_ativo (ativo),
    FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Client IDs
CREATE TABLE client_ids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id VARCHAR(36) UNIQUE NOT NULL,
    provedor_id INT NOT NULL,
    id_revendedor BIGINT NOT NULL,
    usuario VARCHAR(100) NULL,
    ultima_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_client_id (client_id),
    INDEX idx_atividade (ultima_atividade),
    INDEX idx_revendedor (id_revendedor),
    FOREIGN KEY (provedor_id) REFERENCES provedores(id_provedor) ON DELETE CASCADE,
    FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Planos
CREATE TABLE planos (
    id_plano INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    duracao_dias INT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Faturas
CREATE TABLE faturas (
    id_fatura INT AUTO_INCREMENT PRIMARY KEY,
    id_revendedor BIGINT NOT NULL,
    id_plano INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    vencimento DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_revendedor (id_revendedor),
    INDEX idx_status (status),
    INDEX idx_vencimento (vencimento),
    FOREIGN KEY (id_revendedor) REFERENCES revendedores(id_revendedor) ON DELETE CASCADE,
    FOREIGN KEY (id_plano) REFERENCES planos(id_plano) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Pagamentos
CREATE TABLE pagamentos (
    id_pagamento INT AUTO_INCREMENT PRIMARY KEY,
    id_fatura INT NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    metodo_pagamento VARCHAR(50) NULL,
    comprovante TEXT NULL,
    pago_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fatura (id_fatura),
    FOREIGN KEY (id_fatura) REFERENCES faturas(id_fatura) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---

## 🚀 **Deploy & Configuração**

### **📁 Estrutura Final:**
```
🌐 webnoma.space/
├── 📜 index.html                    # Login painéis
├── 📜 admin.html                    # Painel admin
├── 📜 revendedor.html               # Painel master
├── 📜 sub_revendedor.html           # Painel sub
├── 📜 api.js                        # Ponte frontend-backend
├── 📁 api/                          # Backend completo
│   ├── 📜 auth.php                  # Login/logout
│   ├── 📜 verificar_provedor.php    # Porteiro app
│   ├── 📜 validar_login.php         # Vinculador app
│   ├── 📜 verificar_sessao.php      # Resetador app
│   ├── 📜 logo_proxy.php            # Branding
│   ├── 📜 revendedores.php          # CRUD revendedores
│   ├── 📜 provedores.php            # CRUD provedores
│   ├── 📜 client_ids.php            # CRUD ativos
│   ├── 📜 rede_revendedor.php       # Modal rede
│   ├── 📜 stats.php                 # Dashboard
│   ├── 📜 financeiro.php            # Sistema financeiro
│   ├── 📁 config/
│   │   ├── 📜 database_config.php   # Dual SQLite/MySQL
│   │   └── 📜 database_sqlite.php   # Legacy SQLite
│   └── 📜 db.db                     # SQLite desenvolvimento
├── 📁 index/
│   └── 📜 proxy.php                 # Roteador sessões SPA
├── 📁 proxy/html/                   # Sessões navegáveis (app)
│   ├── 📜 index.html
│   ├── 📜 login.html
│   ├── 📜 autenticando.html
│   ├── 📜 home.html
│   ├── 📜 canais.html
│   ├── 📜 filmes.html
│   └── 📜 series.html
├── 📁 uploads/logos/                # Logos personalizadas
│   ├── 📜 12345678.png
│   └── 📜 87654321.jpg
├── 📁 public/logos/                 # Logo padrão
│   └── 📜 nomaapp.png
└── 📁 logs/
    └── 📜 nomatv_v45.log
```

### **✅ Checklist de Deploy:**

#### **1. Preparação do Ambiente:**
```bash
# Criar estrutura de diretórios
mkdir -p /var/www/webnoma.space/{api/config,index,proxy/html,uploads/logos,public/logos,logs}

# Permissões críticas
chmod 755 /var/www/webnoma.space/api/
chmod 666 /var/www/webnoma.space/api/db.db
chmod 755 /var/www/webnoma.space/uploads/logos/
chmod 644 /var/www/webnoma.space/logs/nomatv_v45.log
```

#### **2. Configuração PHP:**
```ini
# php.ini ajustes
session.auto_start = 0
session.use_cookies = 1
session.cookie_httponly = 1
session.cookie_secure = 1
max_execution_time = 30
memory_limit = 128M
upload_max_filesize = 5M
```

#### **3. Configuração MySQL Produção:**
```bash
# Criar usuário MySQL
mysql -u root -p
CREATE USER 'nomatv_user'@'localhost' IDENTIFIED BY 'senha_forte_aqui';
CREATE DATABASE nomatv_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON nomatv_prod.* TO 'nomatv_user'@'localhost';
FLUSH PRIVILEGES;

# Executar migration
mysql -u nomatv_user -p nomatv_prod < migration_mysql.sql
```

#### **4. Variáveis de Ambiente:**
```bash
# .env (produção)
export DB_HOST=localhost
export DB_PORT=3306
export DB_DATABASE=nomatv_prod
export DB_USERNAME=nomatv_user
export DB_PASSWORD=senha_forte_aqui
```

#### **5. Teste de Conectividade:**
```bash
# URLs de teste
curl -X GET "https://webnoma.space/api/stats.php"
curl -X POST "https://webnoma.space/api/auth.php" -d '{"action":"login","usuario":"admin","senha":"123456"}'
curl -X GET "https://webnoma.space/index/proxy.php?page=index"
curl -X GET "https://webnoma.space/api/logo_proxy.php?id=12345678"
```

### **🔧 Configuração Apache/Nginx:**

#### **Apache .htaccess:**
```apache
# .htaccess para webnoma.space
RewriteEngine On

# CORS Headers
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"

# Handle OPTIONS preflight
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

# PHP Error Handling
php_flag display_errors off
php_flag log_errors on
php_value error_log /var/www/webnoma.space/logs/php_errors.log

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### **Nginx Config:**
```nginx
server {
    listen 443 ssl http2;
    server_name webnoma.space;
    root /var/www/webnoma.space;
    
    # SSL Configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    # CORS Headers
    add_header Access-Control-Allow-Origin "*" always;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Content-Type, Authorization" always;
    
    # Handle OPTIONS
    if ($request_method = 'OPTIONS') {
        return 200;
    }
    
    # PHP Handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    # Security
    location ~ /\.ht { deny all; }
    location ~ /\.env { deny all; }
    location ~ /db\.db$ { deny all; }
}
```

### **📊 Monitoramento & Logs:**

#### **Script de Monitoramento:**
```bash
#!/bin/bash
# monitor_nomatv.sh

LOG_FILE="/var/www/webnoma.space/logs/nomatv_v45.log"
ERROR_LOG="/var/www/webnoma.space/logs/php_errors.log"
ALERT_EMAIL="admin@webnoma.space"

# Verificar espaço em disco
DISK_USAGE=$(df /var/www/webnoma.space | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 85 ]; then
    echo "ALERTA: Disco acima de 85%" | mail -s "NomaTV Disk Alert" $ALERT_EMAIL
fi

# Verificar logs de erro
ERROR_COUNT=$(tail -100 $ERROR_LOG | grep "$(date +%Y-%m-%d)" | wc -l)
if [ $ERROR_COUNT -gt 10 ]; then
    echo "ALERTA: Muitos erros PHP hoje: $ERROR_COUNT" | mail -s "NomaTV Error Alert" $ALERT_EMAIL
fi

# Verificar conectividade MySQL
mysql -u nomatv_user -p$DB_PASSWORD -e "SELECT 1" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "ALERTA: MySQL não responsivo" | mail -s "NomaTV MySQL Alert" $ALERT_EMAIL
fi
```

#### **Logrotate Configuration:**
```
# /etc/logrotate.d/nomatv
/var/www/webnoma.space/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    copytruncate
}
```

### **🔒 Segurança em Produção:**

#### **1. Hardening PHP:**
```ini
# php.ini security
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
display_errors = Off
log_errors = On
```

#### **2. Firewall Rules:**
```bash
# UFW rules
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw deny 3306/tcp   # MySQL (apenas local)
ufw enable
```

#### **3. Backup Strategy:**
```bash
#!/bin/bash
# backup_nomatv.sh

BACKUP_DIR="/backup/nomatv/$(date +%Y%m%d)"
mkdir -p $BACKUP_DIR

# Backup MySQL
mysqldump -u nomatv_user -p$DB_PASSWORD nomatv_prod > $BACKUP_DIR/database.sql

# Backup arquivos
tar -czf $BACKUP_DIR/files.tar.gz /var/www/webnoma.space/

# Cleanup backups antigos (manter 30 dias)
find /backup/nomatv/ -type d -mtime +30 -exec rm -rf {} \;
```

---

## 📊 **Resumo da Arquitetura Completa**

### **🔄 Fluxo de Integração Total:**
```
📱 App Smart TV (ES5) ↔ 🌐 Backend API v4.5 ↔ 🖥️ Painéis Web (ES6+)
```

### **📈 Performance Targets:**
- **App Startup:** < 3s (vs Netflix 3-5s)
- **API Response:** < 200ms endpoints
- **Cache Hit Rate:** > 90% app
- **Database Queries:** < 50ms average
- **Memory Usage:** < 80MB app, < 128MB backend

### **✅ Recursos Implementados:**
- [x] **App Smart TV completo** (7 sessões ES5)
- [x] **Backend API v4.5** (12 endpoints + hierarquia)
- [x] **3 Painéis administrativos** (admin/master/sub)
- [x] **Sistema branding hierárquico**
- [x] **Storage híbrido 4 camadas**
- [x] **Sistema financeiro completo**
- [x] **MySQL produção + SQLite dev**
- [x] **Deploy strategy completa**
- [x] **Monitoramento & logs**
- [x] **Segurança hardening**

### **🚀 Status do Projeto:**
**PRODUCTION-READY** - Sistema completo funcional com documentação técnica como fonte única da verdade.

---

**📄 Documento Técnico - NomaTV Completo v2.0**  
**Arquitetura:** App Smart TV + Backend + Painéis | **Status:** Production-Ready | **Data:** 14/09/2025

> **Esta é a documentação técnica completa do sistema NomaTV. Contém todas as especificações necessárias para desenvolvimento, deploy e manutenção do projeto completo.**
