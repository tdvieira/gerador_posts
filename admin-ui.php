<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Obter chaves salvas
$gemini_key = get_option( 'gpg_gemini_api_key', '' );
$openai_key = get_option( 'gpg_openai_api_key', '' );
$groq_key   = get_option( 'gpg_groq_api_key', '' );
$puter_key  = get_option( 'gpg_puter_api_key', '' );

$masked_gemini = '';
if ( ! empty( $gemini_key ) ) {
	$masked_gemini = substr( $gemini_key, 0, 4 ) . str_repeat( '*', 12 ) . substr( $gemini_key, -4 );
}

$masked_openai = '';
if ( ! empty( $openai_key ) ) {
	$masked_openai = substr( $openai_key, 0, 4 ) . str_repeat( '*', 12 ) . substr( $openai_key, -4 );
}

$masked_groq = '';
if ( ! empty( $groq_key ) ) {
	$masked_groq = substr( $groq_key, 0, 4 ) . str_repeat( '*', 12 ) . substr( $groq_key, -4 );
}

$masked_puter = '';
if ( ! empty( $puter_key ) ) {
	$len = strlen( $puter_key );
	if ( $len > 8 ) {
		$masked_puter = substr( $puter_key, 0, 4 ) . str_repeat( '*', 12 ) . substr( $puter_key, -4 );
	} else {
		$masked_puter = str_repeat( '*', $len );
	}
}

// Lista de Categorias do Blog
$blog_categories = array(
	'Benefícios de ter um Site',
	'Design e Experiência do Usuário',
	'Dicas e Boas Práticas',
	'Histórias de Sucesso',
	'Marketing Digital e E-commerce',
	'Segurança e Manutenção',
	'Tendências e Novidades',
	'Tutoriais Simples'
);
?>

<!-- Importar Fonte Google Outfit para Imagens Gratuitas -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<?php if ( ! empty( $puter_key ) ) : ?>
<script>
	puter.authToken = '<?php echo esc_js( $puter_key ); ?>';
</script>
<?php endif; ?>

<div id="gpg-plugin-container">
	<!-- Cabeçalho Principal -->
	<header class="gpg-header">
		<div class="gpg-header-brand">
			<span class="gpg-logo-icon">
				<svg class="gpg-logo-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
				</svg>
			</span>
			<div class="gpg-brand-texts">
				<h1>Gerador de Posts</h1>
				<p>Textos e Imagens Inteligentes com IA</p>
			</div>
		</div>
		<div class="gpg-tabs">
			<button class="gpg-tab-btn active cursor-pointer" data-tab="generator-tab">
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
				Gerador
			</button>
			<button class="gpg-tab-btn cursor-pointer" data-tab="batch-tab">
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
				Agendador em Lote
			</button>
			<button class="gpg-tab-btn cursor-pointer" data-tab="settings-tab">
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
				Configurações
			</button>
		</div>
	</header>

	<div class="gpg-content-area">
		<!-- TAB 1: GERADOR INDIVIDUAL -->
		<section id="generator-tab" class="gpg-tab-content active">
			<div class="gpg-grid">
				<!-- Painel de Inputs -->
				<div class="gpg-card gpg-form-card">
					<div class="gpg-card-header">
						<svg class="gpg-card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
						</svg>
						<div>
							<h2 class="gpg-card-title">Nova Publicação</h2>
							<p class="gpg-card-subtitle">Gere um artigo individual com imagens e agendamento opcional.</p>
						</div>
					</div>

					<form id="gpg-generation-form">
						<!-- Seleção de Modelos de Texto -->
						<div class="gpg-form-row">
							<div class="gpg-form-group half">
								<label for="gpg-text-provider">Provedor IA Texto <span class="required">*</span></label>
								<select id="gpg-text-provider" name="text_provider" class="cursor-pointer" required>
									<option value="" disabled selected>Escolha o Provedor de Texto...</option>
									<option value="gemini">Google Gemini</option>
									<option value="openai">OpenAI (GPT)</option>
									<option value="groq">Groq (Llama / Grátis)</option>
								</select>
							</div>
							<div class="gpg-form-group half">
								<label for="gpg-text-model">Modelo de Texto</label>
								<select id="gpg-text-model" name="text_model" class="cursor-pointer">
									<!-- Dinâmico -->
								</select>
							</div>
						</div>

						<div class="gpg-form-group">
							<label for="gpg-topic">Título <span class="required">*</span></label>
							<input type="text" id="gpg-topic" name="topic" placeholder="Ex: Impacto da velocidade do site no SEO" required />
						</div>

						<div class="gpg-advanced-toggle-wrapper">
							<button type="button" id="gpg-toggle-advanced-btn" class="gpg-toggle-advanced-link cursor-pointer">
								<svg class="gpg-svg-icon" style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
								</svg>
								Configurações Avançadas de SEO
							</button>
						</div>

						<div id="gpg-advanced-seo-box" class="gpg-advanced-seo-container" style="display: none;">
							<div class="gpg-form-group" style="margin-bottom: 0;">
								<label for="gpg-keywords">Palavras-chave de Foco</label>
								<input type="text" id="gpg-keywords" name="keywords" placeholder="Ex: velocidade do site, otimização de imagens" />
								<span class="field-desc" style="margin-bottom: 5px;">Separadas por vírgula. Se deixadas vazias, a IA gerará automaticamente e salvará no Rank Math.</span>
							</div>
						</div>

						<div class="gpg-form-row">
							<div class="gpg-form-group half">
								<label for="gpg-tone">Tom de Voz <span class="required">*</span></label>
								<select id="gpg-tone" name="tone" class="cursor-pointer" required>
									<option value="" disabled selected>Escolha o tom de voz...</option>
									<option value="Educativo e Didático">Educativo e Didático</option>
									<option value="Profissional e Corporativo">Profissional e Corporativo</option>
									<option value="Informal e Amigável">Informal e Amigável</option>
									<option value="Persuasivo e Vendas">Persuasivo e Vendas</option>
								</select>
							</div>

							<div class="gpg-form-group half">
								<label for="gpg-length">Tamanho Estimado <span class="required">*</span></label>
								<select id="gpg-length" name="length" class="cursor-pointer" required>
									<option value="" disabled selected>Escolha o tamanho...</option>
									<option value="short">Curto (~500 palavras)</option>
									<option value="medium">Médio (~1000 palavras)</option>
									<option value="long">Longo (~1500+ palavras)</option>
									<option value="extralong">Extra Longo (~3000+ palavras)</option>
								</select>
							</div>
						</div>

						<div class="gpg-form-row">
							<div class="gpg-form-group half">
								<label for="gpg-category">Categoria do Post <span class="required">*</span></label>
								<select id="gpg-category" name="category" class="cursor-pointer" required>
									<option value="" disabled selected>Escolha a categoria...</option>
									<?php foreach ( $blog_categories as $cat ) : ?>
										<option value="<?php echo esc_attr( $cat ); ?>"><?php echo esc_html( $cat ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="gpg-form-group half">
								<label for="gpg-publish-date">Data/Hora de Publicação</label>
								<input type="datetime-local" id="gpg-publish-date" name="publish_date" class="cursor-pointer" />
								<span class="field-desc">Deixe em branco para publicar agora ou salvar como rascunho.</span>
							</div>
						</div>

						<!-- Configurações de Imagem de Destaque -->
						<div class="gpg-divider"></div>
						
						<div class="gpg-card-header" style="margin-bottom: 15px;">
							<svg class="gpg-card-header-icon secondary-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
							</svg>
							<div>
								<h3 class="gpg-sub-title" style="margin: 0;">Imagem de Destaque</h3>
							</div>
						</div>

						<div class="gpg-form-row">
							<div class="gpg-form-group half">
								<label for="gpg-image-provider">Provedor IA Imagem <span class="required">*</span></label>
								<select id="gpg-image-provider" name="image_provider" class="cursor-pointer" required>
									<option value="" disabled selected>Escolha o Provedor de Imagem...</option>
									<option value="openai">OpenAI (GPT-2)</option>
									<option value="gemini">Google (Nano Banana)</option>
									<option value="none">Nenhum (Apenas Texto)</option>
								</select>
							</div>
							<div class="gpg-form-group half">
								<label for="gpg-image-model">Modelo de Imagem</label>
								<select id="gpg-image-model" name="image_model" class="cursor-pointer">
									<!-- Dinâmico -->
								</select>
							</div>
						</div>

						<button type="submit" id="gpg-generate-btn" class="gpg-btn-primary cursor-pointer">
							<span class="btn-text">
								<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
								Gerar Artigo
							</span>
							<div class="btn-loader-spinner"></div>
						</button>
					</form>
				</div>

				<!-- Visualização e Pré-visualização -->
				<div class="gpg-card gpg-preview-card">
					<!-- Estado Vazio -->
					<div class="preview-empty-state">
						<span class="preview-icon">
							<svg class="gpg-svg-icon-large" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0l-3-3a4 4 0 010-5.656l3-3a4 4 0 015.656 0l3 3a4 4 0 010 5.656l-3 3z"></path>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 12l9 9m-6-15h.01M9 15h.01M16 20h.01"></path>
							</svg>
						</span>
						<p>Insira as diretrizes ao lado para criar o texto e gerar as imagens estruturadas.</p>
					</div>

					<!-- Estado Carregando Artigo -->
					<div class="preview-loading-state" style="display: none;">
						<div class="preview-loading-header">
							<span class="pulsing-brain" style="margin-bottom: 0; display: flex; align-items: center;">
								<svg class="gpg-svg-icon-huge pulsing-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 42px; height: 42px;">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
								</svg>
							</span>
							<div style="flex: 1; margin-left: 15px;">
								<h3>IA Criando Artigo</h3>
								<p class="loading-status-text" id="gpg-loading-status-msg">Iniciando a redação do post...</p>
								<div class="loading-progress-bar">
									<div class="progress-bar-fill"></div>
								</div>
							</div>
						</div>
						<div class="gpg-skeleton-simulator">
							<div class="gpg-skeleton-block gpg-skeleton-title"></div>
							<div class="gpg-skeleton-block gpg-skeleton-meta"></div>
							<div class="gpg-skeleton-block gpg-skeleton-image"></div>
							<div class="gpg-skeleton-paragraph">
								<div class="gpg-skeleton-block gpg-skeleton-line"></div>
								<div class="gpg-skeleton-block gpg-skeleton-line w-90"></div>
								<div class="gpg-skeleton-block gpg-skeleton-line w-75"></div>
							</div>
							<div class="gpg-skeleton-toc">
								<div class="gpg-skeleton-toc-title"></div>
								<div class="gpg-skeleton-block gpg-skeleton-line w-50"></div>
								<div class="gpg-skeleton-block gpg-skeleton-line w-50"></div>
							</div>
							<div class="gpg-skeleton-paragraph">
								<div class="gpg-skeleton-block gpg-skeleton-line"></div>
								<div class="gpg-skeleton-block gpg-skeleton-line w-90"></div>
							</div>
						</div>
					</div>

					<!-- Estado Resultados -->
					<div class="preview-result-state" style="display: none;">
						<div class="preview-header-actions">
							<span class="status-badge">Artigo Gerado</span>
							<button id="gpg-save-draft-btn" class="gpg-btn-success cursor-pointer">
								<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
								Salvar Publicação
							</button>
						</div>

						<!-- Editor de Título -->
						<div class="gpg-form-group">
							<label for="gpg-res-title">Título do Artigo</label>
							<input type="text" id="gpg-res-title" class="gpg-input-editable" />
							<div class="char-count"><span id="title-char-count">0</span> / 70 caracteres (Mínimo 65)</div>
						</div>

						<!-- URL Amigável (Slug) -->
						<div class="gpg-form-group">
							<label for="gpg-res-slug">URL Amigável (Slug) <span class="required">*</span></label>
							<input type="text" id="gpg-res-slug" class="gpg-input-editable" style="font-weight: normal; font-size: 14px;" placeholder="Ex: impacto-da-velocidade-do-site-no-seo" />
							<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
								<span id="gpg-slug-warning" style="font-size: 12px; color: var(--gpg-error); font-weight: 500; display: none;">⚠ A palavra-chave não foi encontrada na URL!</span>
								<span style="flex: 1;"></span>
								<div class="char-count" style="margin-top: 0;"><span id="slug-char-count">0</span> / 75 caracteres</div>
							</div>
						</div>

						<!-- Editor de Meta Descrição -->
						<div class="gpg-form-group">
							<label for="gpg-res-meta">Meta Descrição (Rank Math)</label>
							<textarea id="gpg-res-meta" class="gpg-textarea-editable" rows="2"></textarea>
							<div class="char-count"><span id="meta-char-count">0</span> / 138 caracteres</div>
						</div>

						<!-- Editor de Resumo do Post -->
						<div class="gpg-form-group">
							<label for="gpg-res-excerpt">Resumo do Post (Excerpt - WordPress/Rank Math)</label>
							<textarea id="gpg-res-excerpt" class="gpg-textarea-editable" rows="2" placeholder="Resumo do post gerado pela IA (160 a 175 caracteres)..."></textarea>
							<div class="char-count"><span id="excerpt-char-count">0</span> / 175 caracteres</div>
						</div>

						<!-- Imagem 1: Destaque / Topo -->
						<div id="gpg-img-box-1" class="gpg-image-module-box">
							<label>Imagem 1 (Destaque e Topo - Proporção 16:9)</label>
							<div class="gpg-image-preview-container">
								<div class="img-state-empty empty-1">
									<svg class="gpg-svg-icon-large" style="color: #a0aec0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
									</svg>
									<p>Imagem não gerada.</p>
								</div>
								<div class="img-state-loading loading-1" style="display:none;">
									<div class="pulsing-image">
										<svg class="gpg-svg-icon-large pulsing-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
										</svg>
									</div>
									<p>Gerando imagem 1...</p>
								</div>
								<div class="img-state-ready ready-1" style="display:none;"><img id="gpg-preview-img-1" class="widescreen-img" src="" alt="Imagem 1" /></div>
							</div>
							<div class="gpg-image-prompt-box">
								<textarea id="gpg-res-prompt-1" class="gpg-textarea-editable" rows="3" placeholder="Prompt em inglês para a imagem 1..."></textarea>
								<button type="button" class="gpg-trigger-img-btn gpg-btn-secondary mini-btn cursor-pointer" data-img-index="1">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
									Gerar Imagem 1
								</button>
							</div>
						</div>

						<!-- Imagem 2: Corpo / Interna -->
						<div id="gpg-img-box-2" class="gpg-image-module-box">
							<label>Imagem 2 (Inserida no Corpo - Proporção 16:9)</label>
							<div class="gpg-image-preview-container">
								<div class="img-state-empty empty-2">
									<svg class="gpg-svg-icon-large" style="color: #a0aec0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
									</svg>
									<p>Imagem não gerada.</p>
								</div>
								<div class="img-state-loading loading-2" style="display:none;">
									<div class="pulsing-image">
										<svg class="gpg-svg-icon-large pulsing-animation" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
										</svg>
									</div>
									<p>Gerando imagem 2...</p>
								</div>
								<div class="img-state-ready ready-2" style="display:none;"><img id="gpg-preview-img-2" class="widescreen-img" src="" alt="Imagem 2" /></div>
							</div>
							<div class="gpg-image-prompt-box">
								<textarea id="gpg-res-prompt-2" class="gpg-textarea-editable" rows="3" placeholder="Prompt em inglês para a imagem 2..."></textarea>
								<button type="button" class="gpg-trigger-img-btn gpg-btn-secondary mini-btn cursor-pointer" data-img-index="2">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
									Gerar Imagem 2
								</button>
							</div>
						</div>

						<!-- Editor de Conteúdo HTML -->
						<div class="gpg-form-group">
							<label>Corpo do Artigo (HTML Editável)</label>
							<div id="gpg-res-content-editor" contenteditable="true" class="gpg-editor-editable"></div>
						</div>
					</div>
					
					<!-- Estado Sucesso (Painel pós-salvamento) -->
					<div class="preview-success-state" style="display: none;"></div>
				</div>
			</div>
		</section>

		<!-- TAB 2: AGENDAMENTO EM LOTE -->
		<section id="batch-tab" class="gpg-tab-content">
			<div class="gpg-card">
				<div class="gpg-card-header">
					<svg class="gpg-card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path>
					</svg>
					<div>
						<h2 class="gpg-card-title">Agendador em Lote</h2>
						<p class="gpg-card-subtitle">Insira múltiplos temas e configure datas específicas. O plugin gerará e agendará cada um na fila.</p>
					</div>
				</div>

				<!-- Progresso do Lote -->
				<div id="gpg-batch-progress-box" class="gpg-batch-progress" style="display: none;">
					<div class="progress-info-row">
						<span id="gpg-batch-progress-text">Processando fila: 0 de 0 posts</span>
						<span id="gpg-batch-progress-pct">0%</span>
					</div>
					<div class="loading-progress-bar" style="margin: 10px 0 20px 0;">
						<div id="gpg-batch-progress-fill" class="progress-bar-fill" style="width: 0%; animation: none;"></div>
					</div>
				</div>

				<!-- Configurações Gerais do Lote -->
				<div class="gpg-batch-global-configs">
					<div class="gpg-form-row">
						<div class="gpg-form-group half">
							<label for="gpg-batch-text-provider">Provedor IA Texto <span class="required">*</span></label>
							<select id="gpg-batch-text-provider" class="cursor-pointer" required>
								<option value="" disabled selected>Escolha o Provedor de Texto...</option>
								<option value="gemini">Google Gemini</option>
								<option value="openai">OpenAI (GPT)</option>
								<option value="groq">Groq (Llama / Grátis)</option>
							</select>
						</div>
						<div class="gpg-form-group half">
							<label for="gpg-batch-image-provider">Provedor IA Imagem <span class="required">*</span></label>
							<select id="gpg-batch-image-provider" class="cursor-pointer" required>
								<option value="" disabled selected>Escolha o Provedor de Imagem...</option>
								<option value="openai">OpenAI (GPT-2)</option>
								<option value="gemini">Google (Nano Banana)</option>
								<option value="none">Nenhum</option>
							</select>
						</div>
						<div class="gpg-form-group half">
							<label for="gpg-batch-length">Tamanho Estimado <span class="required">*</span></label>
							<select id="gpg-batch-length" class="cursor-pointer" required>
								<option value="" disabled selected>Escolha o tamanho...</option>
								<option value="short">Curto (~500 palavras)</option>
								<option value="medium">Médio (~1000 palavras)</option>
								<option value="long">Longo (~1500+ palavras)</option>
								<option value="extralong">Extra Longo (~3000+ palavras)</option>
							</select>
						</div>
						<div class="gpg-form-group half">
							<label for="gpg-batch-tone">Tom de Voz <span class="required">*</span></label>
							<select id="gpg-batch-tone" class="cursor-pointer" required>
								<option value="" disabled selected>Escolha o tom de voz...</option>
								<option value="Educativo e Didático">Educativo e Didático</option>
								<option value="Profissional e Corporativo">Profissional e Corporativo</option>
								<option value="Informal e Amigável">Informal e Amigável</option>
								<option value="Persuasivo e Vendas">Persuasivo e Vendas</option>
							</select>
						</div>
					</div>

					<!-- Linha oculta para os modelos, alinhada geometricamente com os provedores de cima -->
					<div class="gpg-form-row" id="gpg-batch-models-row" style="margin-top: 15px; display: none;">
						<div class="gpg-form-group gpg-batch-model-col" id="gpg-batch-text-model-wrapper" style="display: none;">
							<label for="gpg-batch-text-model">Modelo de Texto <span class="required">*</span></label>
							<select id="gpg-batch-text-model" class="cursor-pointer" required>
								<!-- Dinâmico -->
							</select>
						</div>
						<div class="gpg-form-group gpg-batch-model-col" id="gpg-batch-image-model-wrapper" style="display: none;">
							<label for="gpg-batch-image-model">Modelo de Imagem <span class="required">*</span></label>
							<select id="gpg-batch-image-model" class="cursor-pointer" required>
								<!-- Dinâmico -->
							</select>
						</div>
					</div>
				</div>

				<div class="gpg-table-responsive">
					<table class="gpg-batch-table" id="gpg-batch-table">
						<thead>
							<tr>
								<th style="width: 40px; text-align: center;"><input type="checkbox" id="gpg-batch-select-all" class="cursor-pointer" checked /></th>
								<th>Conteúdo / Pipeline</th>
								<th style="width: 250px;">Progresso & Status</th>
								<th style="width: 180px; text-align: right;">Ações</th>
							</tr>
						</thead>
						<tbody>
							<!-- Linhas dinâmicas -->
						</tbody>
					</table>
				</div>

				<div class="gpg-table-actions">
					<button type="button" id="gpg-batch-add-row" class="gpg-btn-secondary cursor-pointer">
						<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
						Adicionar Post
					</button>
					<button type="button" id="gpg-batch-process-btn" class="gpg-btn-primary cursor-pointer" style="width: auto;">
						<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
						Processar Fila e Agendar
					</button>
				</div>
			</div>
		</section>

		<!-- TAB 3: CONFIGURAÇÕES -->
		<section id="settings-tab" class="gpg-tab-content">
			<div class="gpg-card gpg-settings-card">
				<div class="gpg-card-header" style="margin-bottom: 25px; border-bottom: 1px solid var(--gpg-border-color); padding-bottom: 15px;">
					<svg class="gpg-card-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
					</svg>
					<div>
						<h2 class="gpg-card-title">Configurações do Plugin</h2>
						<p class="gpg-card-subtitle">Gerencie as chaves de API e tokens de autenticação para as inteligências de texto e imagem.</p>
					</div>
				</div>

				<form id="gpg-settings-form">
					<div class="gpg-settings-grid">
						
						<!-- Coluna da Esquerda: APIs de Geração de Texto -->
						<div class="gpg-settings-section-card">
							<div class="gpg-settings-section-header">
								<span class="gpg-settings-section-icon-wrap" style="background-color: rgba(250, 91, 15, 0.06);">
									<svg class="gpg-svg-icon" style="color: var(--gpg-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
									</svg>
								</span>
								<div>
									<h3 class="gpg-settings-section-title">Geração de Texto</h3>
									<p class="gpg-settings-section-subtitle">Configurações para criação e estruturação de artigos.</p>
								</div>
							</div>

							<!-- Google Gemini -->
							<div class="gpg-settings-field-box">
								<div class="gpg-settings-field-title-row">
									<label for="gpg-settings-gemini-key">
										<span class="provider-label">Google Gemini API</span>
									</label>
									<?php if ( ! empty( $masked_gemini ) ) : ?>
										<span class="gpg-status-badge success-badge">✓ Ativo</span>
									<?php else : ?>
										<span class="gpg-status-badge alert-badge">Inativo</span>
									<?php endif; ?>
								</div>
								<input type="password" id="gpg-settings-gemini-key" name="gemini_api_key" placeholder="Insira a API Key do Gemini..." value="<?php echo esc_attr( $gemini_key ); ?>" />
								<?php if ( ! empty( $masked_gemini ) ) : ?>
									<span class="field-desc">Chave ativa: <code><?php echo esc_html( $masked_gemini ); ?></code></span>
								<?php else : ?>
									<span class="field-desc">Obtenha gratuitamente no <a href="https://aistudio.google.com/" target="_blank" rel="noreferrer" class="cursor-pointer">Google AI Studio</a>.</span>
								<?php endif; ?>
							</div>

							<!-- OpenAI (ChatGPT) -->
							<div class="gpg-settings-field-box">
								<div class="gpg-settings-field-title-row">
									<label for="gpg-settings-openai-key">
										<span class="provider-label">OpenAI API (GPT)</span>
									</label>
									<?php if ( ! empty( $masked_openai ) ) : ?>
										<span class="gpg-status-badge success-badge">✓ Ativo</span>
									<?php else : ?>
										<span class="gpg-status-badge alert-badge">Inativo</span>
									<?php endif; ?>
								</div>
								<input type="password" id="gpg-settings-openai-key" name="openai_api_key" placeholder="Insira a API Key da OpenAI..." value="<?php echo esc_attr( $openai_key ); ?>" />
								<?php if ( ! empty( $masked_openai ) ) : ?>
									<span class="field-desc">Chave ativa: <code><?php echo esc_html( $masked_openai ); ?></code></span>
								<?php else : ?>
									<span class="field-desc">Obtenha na plataforma de desenvolvedores <a href="https://platform.openai.com/api-keys" target="_blank" rel="noreferrer" class="cursor-pointer">OpenAI Platform</a>.</span>
								<?php endif; ?>
							</div>

							<!-- Groq (Llama) -->
							<div class="gpg-settings-field-box" style="margin-bottom: 0;">
								<div class="gpg-settings-field-title-row">
									<label for="gpg-settings-groq-key">
										<span class="provider-label">Groq API (Llama / Grátis)</span>
									</label>
									<?php if ( ! empty( $masked_groq ) ) : ?>
										<span class="gpg-status-badge success-badge">✓ Ativo</span>
									<?php else : ?>
										<span class="gpg-status-badge alert-badge">Inativo</span>
									<?php endif; ?>
								</div>
								<input type="password" id="gpg-settings-groq-key" name="groq_api_key" placeholder="Insira a API Key do Groq..." value="<?php echo esc_attr( $groq_key ); ?>" />
								<?php if ( ! empty( $masked_groq ) ) : ?>
									<span class="field-desc">Chave ativa: <code><?php echo esc_html( $masked_groq ); ?></code></span>
								<?php else : ?>
									<span class="field-desc">Obtenha gratuitamente no console do <a href="https://console.groq.com/keys" target="_blank" rel="noreferrer" class="cursor-pointer">Groq Cloud Console</a>.</span>
								<?php endif; ?>
							</div>
						</div>

						<!-- Coluna da Direita: APIs de Geração de Imagem -->
						<div class="gpg-settings-section-card">
							<div class="gpg-settings-section-header">
								<span class="gpg-settings-section-icon-wrap" style="background-color: rgba(16, 185, 129, 0.06);">
									<svg class="gpg-svg-icon" style="color: var(--gpg-success);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
									</svg>
								</span>
								<div>
									<h3 class="gpg-settings-section-title">Geração de Imagem</h3>
									<p class="gpg-settings-section-subtitle">Configurações para criação de ilustrações e fotos 16:9.</p>
								</div>
							</div>

							<!-- Google Imagen 4 (Usa a mesma API Key do Gemini se configurada) -->
							<div class="gpg-settings-field-box">
								<div class="gpg-settings-field-title-row">
									<label>
										<span class="provider-label">Google Imagen 4</span>
									</label>
									<?php if ( ! empty( $gemini_key ) ) : ?>
										<span class="gpg-status-badge success-badge">✓ Ativo</span>
									<?php else : ?>
										<span class="gpg-status-badge alert-badge">Inativo</span>
									<?php endif; ?>
								</div>
								<input type="text" disabled placeholder="Usa a mesma chave configurada no Gemini (Texto)" value="<?php echo esc_attr( !empty($gemini_key) ? 'Habilitado via chave do Gemini' : 'Insira a chave do Gemini ao lado para habilitar' ); ?>" style="opacity: 0.7; background-color: #f1f5f9; cursor: not-allowed;" />
								<span class="field-desc">É configurada automaticamente ao cadastrar a chave do Gemini.</span>
							</div>

							<!-- OpenAI DALL-E (Usa a mesma API Key da OpenAI se configurada) -->
							<div class="gpg-settings-field-box">
								<div class="gpg-settings-field-title-row">
									<label>
										<span class="provider-label">OpenAI DALL-E 3</span>
									</label>
									<?php if ( ! empty( $openai_key ) ) : ?>
										<span class="gpg-status-badge success-badge">✓ Ativo</span>
									<?php else : ?>
										<span class="gpg-status-badge alert-badge">Inativo</span>
									<?php endif; ?>
								</div>
								<input type="text" disabled placeholder="Usa a mesma chave configurada na OpenAI (Texto)" value="<?php echo esc_attr( !empty($openai_key) ? 'Habilitado via chave da OpenAI' : 'Insira a chave da OpenAI ao lado para habilitar' ); ?>" style="opacity: 0.7; background-color: #f1f5f9; cursor: not-allowed;" />
								<span class="field-desc">É configurada automaticamente ao cadastrar a chave da OpenAI.</span>
							</div>

							<!-- Puter.js (Flux) -->
							<div class="gpg-settings-field-box">
								<div class="gpg-settings-field-title-row">
									<label for="gpg-settings-puter-key">
										<span class="provider-label">Puter.js (Flux / Grátis)</span>
									</label>
									<?php if ( ! empty( $masked_puter ) ) : ?>
										<span class="gpg-status-badge success-badge">✓ Ativo</span>
									<?php else : ?>
										<span class="gpg-status-badge alert-badge">Inativo</span>
									<?php endif; ?>
								</div>
								<input type="password" id="gpg-settings-puter-key" name="puter_api_key" placeholder="Insira o Token do Puter..." value="<?php echo esc_attr( $puter_key ); ?>" />
								<?php if ( ! empty( $masked_puter ) ) : ?>
									<span class="field-desc">Token ativo: <code><?php echo esc_html( $masked_puter ); ?></code></span>
								<?php else : ?>
									<span class="field-desc">Adicione o Auth Token do <a href="https://puter.com/dashboard#account" target="_blank" rel="noreferrer" class="cursor-pointer">Puter</a> para gerar imagens.</span>
								<?php endif; ?>
							</div>
						</div>
						
					</div>

					<div class="gpg-settings-footer">
						<button type="submit" id="gpg-save-settings-btn" class="gpg-btn-primary cursor-pointer" style="width: auto; padding: 12px 35px; margin-top: 20px;">
							<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
							Salvar Todas as Configurações
						</button>
					</div>
				</form>
			</div>
		</section>
	</div>
</div>
