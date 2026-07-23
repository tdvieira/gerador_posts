jQuery(document).ready(function($) {
	const nonce = gpgAdminData.nonce;

	// Alternar seção de Opções Avançadas de SEO
	$('#gpg-toggle-advanced-btn').on('click', function() {
		const btn = $(this);
		const container = $('#gpg-advanced-seo-box');
		container.slideToggle(200, function() {
			if (container.is(':visible')) {
				btn.addClass('active');
				btn.html(`
					<svg class="gpg-svg-icon" style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path>
					</svg>
					Ocultar Configurações de SEO
				`);
			} else {
				btn.removeClass('active');
				btn.html(`
					<svg class="gpg-svg-icon" style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
					</svg>
					Configurações Avançadas de SEO
				`);
			}
		});
	});

	// Modelos de Texto
	const textModels = {
		gemini: [
			{ value: 'gemini-3.5-flash', label: '3.5 Flash' },
			{ value: 'gemini-3.1-pro', label: '3.1 Pro' },
			{ value: 'gemini-3.1-flash-lite', label: '3.1 Flash Lite' }
		],
		openai: [
			{ value: 'gpt-5.5', label: 'GPT-5.5' },
			{ value: 'gpt-5', label: 'GPT-5' },
			{ value: 'gpt-5-mini', label: 'GPT-5 mini' }
		],
		groq: [
			{ value: 'gpt-oss-120b', label: 'GPT OSS 120B' },
			{ value: 'qwen-3.6-27b', label: 'Qwen3.6 27B' },
			{ value: 'llama-3.1-8b', label: 'Llama-3.1-8b' }
		]
	};

	// Modelos de Imagem
	const imageModels = {
		openai: [
			{ value: 'gpt-2', label: 'GPT-2' }
		],
		gemini: [
			{ value: 'nano-banana-pro', label: 'Nano Banana Pro' },
			{ value: 'nano-banana-2', label: 'Nano Banana 2' },
			{ value: 'nano-banana', label: 'Nano Banana' }
		],
		none: [
			{ value: 'none', label: 'Sem Imagem de Destaque' }
		]
	};

	const categoriesOptions = gpgAdminData.categoriesOptions;

	// Atualizar Dropdown de Modelos de Texto
	function updateTextModels(provider, targetSelector = '#gpg-text-model') {
		const models = textModels[provider] || [];
		const modelSelect = $(targetSelector);
		modelSelect.empty();
		if (models.length === 0) {
			modelSelect.append(new Option("Escolha o provedor primeiro...", ""));
			modelSelect.prop('disabled', true);
		} else {
			modelSelect.prop('disabled', false);
			models.forEach(function(m) {
				modelSelect.append(new Option(m.label, m.value));
			});
		}
	}

	// Atualizar Dropdown de Modelos de Imagem
	function updateImageModels(provider, targetSelector = '#gpg-image-model') {
		const models = imageModels[provider] || [];
		const modelSelect = $(targetSelector);
		modelSelect.empty();
		if (models.length === 0) {
			modelSelect.append(new Option("Escolha o provedor primeiro...", ""));
			modelSelect.prop('disabled', true);
		} else {
			models.forEach(function(m) {
				modelSelect.append(new Option(m.label, m.value));
			});
			if (provider === 'none') {
				modelSelect.prop('disabled', true);
			} else {
				modelSelect.prop('disabled', false);
			}
		}
	}

	$('#gpg-text-provider').on('change', function() {
		updateTextModels($(this).val() || '');
	});

	$('#gpg-image-provider').on('change', function() {
		updateImageModels($(this).val() || '');
	});

	// Controle de Visibilidade dos Modelos no Lote
	function checkBatchModelsRowVisibility() {
		const textProvider = $('#gpg-batch-text-provider').val() || '';
		const imageProvider = $('#gpg-batch-image-provider').val() || '';
		
		const hasTextModel = (textProvider !== '');
		const hasImageModel = (imageProvider !== '' && imageProvider !== 'none');
		
		if (hasTextModel || hasImageModel) {
			$('#gpg-batch-models-row').show();
		} else {
			$('#gpg-batch-models-row').hide();
		}
	}

	$('#gpg-batch-text-provider').on('change', function() {
		const val = $(this).val() || '';
		updateTextModels(val, '#gpg-batch-text-model');
		if (val !== '') {
			$('#gpg-batch-text-model-wrapper').show();
		} else {
			$('#gpg-batch-text-model-wrapper').hide();
		}
		checkBatchModelsRowVisibility();
	});

	$('#gpg-batch-image-provider').on('change', function() {
		const val = $(this).val() || '';
		updateImageModels(val, '#gpg-batch-image-model');
		if (val !== '' && val !== 'none') {
			$('#gpg-batch-image-model-wrapper').show();
		} else {
			$('#gpg-batch-image-model-wrapper').hide();
		}
		checkBatchModelsRowVisibility();
	});

	// Inicialização Padrão
	updateTextModels($('#gpg-text-provider').val() || '');
	updateImageModels($('#gpg-image-provider').val() || '');

	const initBatchTextProv = $('#gpg-batch-text-provider').val() || '';
	updateTextModels(initBatchTextProv, '#gpg-batch-text-model');
	if (initBatchTextProv !== '') {
		$('#gpg-batch-text-model-wrapper').show();
	}

	const initBatchImgProv = $('#gpg-batch-image-provider').val() || '';
	updateImageModels(initBatchImgProv, '#gpg-batch-image-model');
	if (initBatchImgProv !== '' && initBatchImgProv !== 'none') {
		$('#gpg-batch-image-model-wrapper').show();
	}
	checkBatchModelsRowVisibility();

	// Abas
	$('.gpg-tab-btn').on('click', function() {
		const target = $(this).data('tab');
		$('.gpg-tab-btn').removeClass('active');
		$(this).addClass('active');
		$('.gpg-tab-content').removeClass('active');
		$('#' + target).addClass('active');
	});

	// Salvar Chaves de Configurações
	$('#gpg-settings-form').on('submit', function(e) {
		e.preventDefault();
		const submitBtn = $('#gpg-save-settings-btn');
		submitBtn.prop('disabled', true).text('Salvando chaves...');

		$.post(ajaxurl, {
			action: 'gpg_save_settings',
			gemini_api_key: $('#gpg-settings-gemini-key').val(),
			openai_api_key: $('#gpg-settings-openai-key').val(),
			groq_api_key: $('#gpg-settings-groq-key').val(),
			puter_api_key: $('#gpg-settings-puter-key').val(),
			nonce: nonce
		}, function(response) {
			submitBtn.prop('disabled', false).html(`
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
				Salvar Configurações
			`);
			if (response.success) {
				alert(response.data.message);
				location.reload();
			} else {
				alert('Erro: ' + response.data.message);
			}
		}).fail(function() {
			submitBtn.prop('disabled', false).html(`
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
				Salvar Configurações
			`);
			alert('Erro de comunicação com o servidor.');
		});
	});

	// Monitorar caracteres do título
	$('#gpg-res-title').on('input', function() {
		const len = $(this).val().length;
		$('#title-char-count').text(len);
		if (len < 65 || len > 70) {
			$('#title-char-count').css('color', '#d32f2f');
		} else {
			$('#title-char-count').css('color', '#64748b');
		}
	});

	// Monitorar caracteres da meta descrição
	$('#gpg-res-meta').on('input', function() {
		const len = $(this).val().length;
		$('#meta-char-count').text(len);
		if (len > 138) {
			$('#meta-char-count').css('color', '#d32f2f');
		} else {
			$('#meta-char-count').css('color', '#64748b');
		}
	});

	// Monitorar caracteres do resumo (excerpt)
	$('#gpg-res-excerpt').on('input', function() {
		const len = $(this).val().length;
		$('#excerpt-char-count').text(len);
		if (len < 160 || len > 175) {
			$('#excerpt-char-count').css('color', '#d32f2f');
		} else {
			$('#excerpt-char-count').css('color', '#64748b');
		}
	});

	let image1Source = '';
	let image1Type = '';
	let image2Source = '';
	let image2Type = '';

	// Gerar Post Individual (Texto + prompts de imagem)
	$('#gpg-generation-form').on('submit', function(e) {
		e.preventDefault();
		const generateBtn = $('#gpg-generate-btn');
		
		generateBtn.addClass('btn-loading').prop('disabled', true);
		$('.preview-empty-state').hide();
		$('.preview-result-state').hide();
		$('.preview-success-state').hide().html('');
		$('.preview-loading-state').show();

		image1Source = ''; image1Type = '';
		image2Source = ''; image2Type = '';

		// Mensagens de progresso dinâmicas (UI/UX Pro Max)
		let currentStep = 0;
		const loadingSteps = [
			"Analisando o tema proposto...",
			"Estruturando os tópicos principais...",
			"Redigindo a introdução atraente...",
			"Desenvolvendo as seções com base nas diretrizes...",
			"Gerando o sumário de conteúdo dinâmico...",
			"Configurando palavras-chave de foco (Rank Math)...",
			"Criando prompts otimizados para as imagens 16:9...",
			"Ajustando formatações e links...",
			"Finalizando a redação do post..."
		];
		
		$('#gpg-loading-status-msg').text(loadingSteps[0]);
		
		const statusInterval = setInterval(function() {
			currentStep = (currentStep + 1) % loadingSteps.length;
			$('#gpg-loading-status-msg').text(loadingSteps[currentStep]);
		}, 4000);

		$.post(ajaxurl, {
			action: 'gpg_generate_post',
			text_provider: $('#gpg-text-provider').val(),
			text_model: $('#gpg-text-model').val(),
			topic: $('#gpg-topic').val(),
			keywords: $('#gpg-keywords').val(),
			tone: $('#gpg-tone').val(),
			length: $('#gpg-length').val(),
			category: $('#gpg-category').val(),
			nonce: nonce
		}, function(response) {
			generateBtn.removeClass('btn-loading').prop('disabled', false);
			clearInterval(statusInterval);
			
			if (response.success) {
				const data = response.data;
				
				$('#gpg-res-title').val(data.title);
				$('#gpg-res-slug').val(data.suggested_slug);
				$('#gpg-res-meta').val(data.meta_description);
				$('#gpg-res-excerpt').val(data.excerpt);
				$('#gpg-res-content-editor').html(data.content);

				const titleLen = data.title ? data.title.length : 0;
				$('#title-char-count').text(titleLen);
				if (titleLen < 65 || titleLen > 70) {
					$('#title-char-count').css('color', '#d32f2f');
				} else {
					$('#title-char-count').css('color', '#64748b');
				}

				const metaLen = data.meta_description ? data.meta_description.length : 0;
				$('#meta-char-count').text(metaLen);
				if (metaLen > 138) {
					$('#meta-char-count').css('color', '#d32f2f');
				} else {
					$('#meta-char-count').css('color', '#64748b');
				}
				$('#slug-char-count').text(data.suggested_slug ? data.suggested_slug.length : 0);

				const excerptLen = data.excerpt ? data.excerpt.length : 0;
				$('#excerpt-char-count').text(excerptLen);
				if (excerptLen < 160 || excerptLen > 175) {
					$('#excerpt-char-count').css('color', '#d32f2f');
				} else {
					$('#excerpt-char-count').css('color', '#64748b');
				}

				$('#gpg-res-prompt-1').val(data.image_1_prompt);
				$('#gpg-res-prompt-2').val(data.image_2_prompt);

				if (!$('#gpg-keywords').val() && data.focus_keywords) {
					$('#gpg-keywords').val(data.focus_keywords);
				}

				if (data.suggested_slug) {
					checkSlugKeyword(data.suggested_slug);
				}

				const imageProvider = $('#gpg-image-provider').val();
				if (imageProvider === 'none') {
					$('#gpg-img-box-1, #gpg-img-box-2').hide();
				} else {
					$('#gpg-img-box-1, #gpg-img-box-2').show();
					$('.img-state-ready, .img-state-loading').hide();
					$('.img-state-empty').show();

					// Disparar automaticamente a geração das imagens
					setTimeout(function() {
						$('.gpg-trigger-img-btn[data-img-index="1"]').click();
						$('.gpg-trigger-img-btn[data-img-index="2"]').click();
					}, 150);
				}

				$('.preview-loading-state').hide();
				$('.preview-result-state').show();
			} else {
				$('.preview-loading-state').hide();
				$('.preview-empty-state').show();
				alert('Erro ao gerar artigo: ' + response.data.message);
			}
		}).fail(function() {
			generateBtn.removeClass('btn-loading').prop('disabled', false);
			clearInterval(statusInterval);
			$('.preview-loading-state').hide();
			$('.preview-empty-state').show();
			alert('Falha crítica de comunicação.');
		});
	});

	// Trigger de Geração de Imagem Individual
	$(document).on('click', '.gpg-trigger-img-btn', function() {
		const btn = $(this);
		const index = btn.data('img-index');
		const prompt = $(`#gpg-res-prompt-${index}`).val();
		const imageProvider = $('#gpg-image-provider').val();
		const imageModel = $('#gpg-image-model').val();

		if (!prompt) {
			alert('O prompt da imagem ' + index + ' é obrigatório.');
			return;
		}

		btn.prop('disabled', true).html('Gerando...');
		$(`.empty-${index}`).hide();
		$(`.ready-${index}`).hide();
		$(`.loading-${index}`).show();

		if (imageProvider === 'puter') {
			// Usar Puter.js para gerar a imagem diretamente e de graça no navegador
			puter.ai.txt2img({ prompt: prompt, model: 'gpt-image-2' })
				.then(function(imageElement) {
					btn.prop('disabled', false).html(`
						<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
						Gerar Imagem ${index}
					`);
					$(`.loading-${index}`).hide();

					const base64Source = imageElement.src;

					if (index === 1) {
						image1Source = base64Source;
						image1Type = 'base64';
						$('#gpg-preview-img-1').attr('src', image1Source);
					} else {
						image2Source = base64Source;
						image2Type = 'base64';
						$('#gpg-preview-img-2').attr('src', image2Source);
					}
					$(`.ready-${index}`).show();
				})
				.catch(function(err) {
					btn.prop('disabled', false).html(`
						<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
						Gerar Imagem ${index}
					`);
					$(`.loading-${index}`).hide();
					$(`.empty-${index}`).show();
					const errorMsg = err.message || (typeof err === 'string' ? err : JSON.stringify(err));
					if (errorMsg.includes('closed') || errorMsg.includes('cancel') || errorMsg.includes('popup')) {
						alert('Para gerar imagens gratuitas via Puter, você precisa fazer login no popup que se abre ou cadastrar o seu Token do Puter nas Configurações.');
					} else {
						alert('Erro ao gerar imagem ' + index + ' via Puter: ' + errorMsg);
					}
				});
		} else {
			$.post(ajaxurl, {
				action: 'gpg_generate_image',
				image_provider: imageProvider,
				image_model: imageModel,
				prompt: prompt,
				nonce: nonce
			}, function(response) {
				btn.prop('disabled', false).html(`
					<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
					Gerar Imagem ${index}
				`);
				$(`.loading-${index}`).hide();

				if (response.success) {
					if (index === 1) {
						image1Source = response.data.source;
						image1Type = response.data.type;
						$('#gpg-preview-img-1').attr('src', image1Source);
					} else {
						image2Source = response.data.source;
						image2Type = response.data.type;
						$('#gpg-preview-img-2').attr('src', image2Source);
					}
					$(`.ready-${index}`).show();
				} else {
					$(`.empty-${index}`).show();
					alert('Erro ao gerar imagem ' + index + ': ' + response.data.message);
				}
			}).fail(function() {
				btn.prop('disabled', false).html(`
					<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
					Gerar Imagem ${index}
				`);
				$(`.loading-${index}`).hide();
				$(`.empty-${index}`).show();
				alert('Falha na comunicação de imagem.');
			});
		}
	});

	// Salvar Post Individual
	$(document).on('click', '#gpg-save-draft-btn', function() {
		const saveBtn = $(this);
		const title = $('#gpg-res-title').val();
		const slug = $('#gpg-res-slug').val();
		const metaDesc = $('#gpg-res-meta').val();
		const excerpt = $('#gpg-res-excerpt').val();
		const content = $('#gpg-res-content-editor').html();
		const keywords = $('#gpg-keywords').val();
		const category = $('#gpg-category').val();
		const publishDate = $('#gpg-publish-date').val();

		if (!title || !content) {
			alert('Título e Conteúdo são obrigatórios.');
			return;
		}

		saveBtn.prop('disabled', true).text('Salvando...');

		$.post(ajaxurl, {
			action: 'gpg_save_post',
			title: title,
			suggested_slug: slug,
			meta_description: metaDesc,
			excerpt: excerpt,
			content: content,
			keywords: keywords,
			category: category,
			publish_date: publishDate,
			image_1_source: image1Source,
			image_1_type: image1Type,
			image_2_source: image2Source,
			image_2_type: image2Type,
			nonce: nonce
		}, function(response) {
			saveBtn.prop('disabled', false).html(`
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
				Salvar Publicação
			`);
			
			if (response.success) {
				const editLink = response.data.edit_link;
				const permalink = response.data.permalink;
				const postId = response.data.post_id;
				const statusText = response.data.status;
				const successPanel = $(`
					<div style="background: rgba(16, 185, 129, 0.04); border: 1.5px solid var(--gpg-success); border-radius: var(--gpg-radius-md); padding: 30px; text-align: center; margin-top: 25px; box-shadow: var(--gpg-shadow-md); animation: fadeIn 0.3s ease-out;">
						<svg class="gpg-svg-icon-large" style="color: var(--gpg-success); width: 48px; height: 48px; display: block; margin: 0 auto 15px auto;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
						</svg>
						<h4 style="margin: 0 0 8px 0; color: var(--gpg-success); font-size: 16px; font-weight: 700;">Artigo Salvo com Sucesso!</h4>
						<p style="margin: 0 0 20px 0; color: var(--gpg-text-muted); font-size: 13px;">Seu artigo contendo as imagens e otimizações SEO foi salvo com o status: <strong>${statusText}</strong>.</p>
						<div style="display: flex; gap: 10px; justify-content: center; align-items: center; margin-top: 20px;">
							<a href="${editLink}" class="gpg-btn-primary cursor-pointer" target="_blank" style="display: inline-flex; text-decoration: none; padding: 11px; width: 42px; height: 42px; align-items: center; justify-content: center; margin: 0;" title="Abrir no Editor WordPress">
								<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h14a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
							</a>
							<a href="${permalink}" class="gpg-btn-primary cursor-pointer" target="_blank" style="display: inline-flex; text-decoration: none; padding: 11px; width: 42px; height: 42px; align-items: center; justify-content: center; margin: 0;" title="Ver Post">
								<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
								</svg>
							</a>
							<button type="button" class="btn-action-delete-single gpg-btn-danger cursor-pointer" data-post-id="${postId}" style="display: inline-flex; padding: 11px; width: 42px; height: 42px; align-items: center; justify-content: center; margin: 0; border: none;" title="Excluir Artigo">
								<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
								</svg>
							</button>
						</div>
					</div>
				`);
				$('.preview-result-state').hide();
				$('.preview-success-state').html(successPanel).show();
			} else {
				alert('Erro ao salvar publicação: ' + response.data.message);
			}
		}).fail(function() {
			saveBtn.prop('disabled', false).html(`
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
				Salvar Publicação
			`);
			alert('Falha técnica ao tentar salvar.');
		});
	});

	// Ação de Excluir post único salvo
	$(document).on('click', '.btn-action-delete-single', function(e) {
		e.preventDefault();
		const postId = $(this).data('post-id');
		if (confirm('Deseja realmente remover este post e movê-lo para a lixeira do WordPress?')) {
			const btn = $(this);
			btn.prop('disabled', true);
			$.post(ajaxurl, {
				action: 'gpg_delete_post',
				post_id: postId,
				nonce: nonce
			}, function(response) {
				if (response.success) {
					// Esconder o painel de sucesso e voltar ao estado vazio
					$('.preview-success-state').hide().html('');
					$('.preview-empty-state').show();
					
					// Zerar as configurações do formulário principal
					$('#gpg-generation-form').trigger('reset');
					
					// Disparar evento de mudança nos provedores de IA para limpar e desabilitar os seletores de modelo
					$('#gpg-text-provider').trigger('change');
					$('#gpg-image-provider').trigger('change');
					
					// Resetar variáveis de estado das imagens
					image1Source = '';
					image1Type = '';
					image2Source = '';
					image2Type = '';
					
					// Esconder a caixa de SEO avançado se estivesse aberta
					$('#gpg-advanced-seo-box').hide();
					
					alert('Post movido para a lixeira com sucesso e configurações limpas.');
				} else {
					alert('Erro ao excluir post: ' + response.data.message);
					btn.prop('disabled', false);
				}
			}).fail(function() {
				alert('Erro de rede ao excluir post do WordPress.');
				btn.prop('disabled', false);
			});
		}
	});

	// --- AGENDADOR EM LOTE ---
	let batchRowIndex = 0;
	let isBatchPaused = false;
	let currentBatchIndex = 0;
	let activeXHR = null;
	let simulatedInterval = null;

	// Selecionar / Deselecionar todos
	$(document).on('change', '#gpg-batch-select-all', function() {
		const isChecked = $(this).is(':checked');
		$('.batch-select-checkbox').prop('checked', isChecked);
	});

	// Adicionar Linha à Fila
	function addBatchRow() {
		batchRowIndex++;
		const row = `
			<tr id="batch-row-${batchRowIndex}" class="batch-data-row state-editing" data-row-id="${batchRowIndex}">
				<td class="col-select-id">
					<input type="checkbox" class="batch-select-checkbox cursor-pointer" checked />
				</td>
				<td class="col-content">
					<!-- Modo Edição -->
					<div class="batch-edit-fields">
						<div class="gpg-form-group" style="margin-bottom: 8px;">
							<input type="text" class="batch-topic" placeholder="Digite o tema do post..." required />
						</div>
						<div class="batch-edit-row">
							<div class="batch-edit-col">
								<select class="batch-category cursor-pointer" required>
									${categoriesOptions}
								</select>
							</div>
							<div class="batch-edit-col">
								<input type="datetime-local" class="batch-date cursor-pointer" />
							</div>
						</div>
					</div>
					<!-- Modo Progresso (Oculto inicialmente) -->
					<div class="batch-progress-fields" style="display: none;">
						<div class="batch-post-title-row">
							<span class="batch-post-id">#${batchRowIndex}</span>
							<span class="batch-post-title-text"></span>
						</div>
						<div class="gpg-batch-pipeline">
							<div class="gpg-pipeline-step step-pending" data-step="INTERP">
								<div class="gpg-pipeline-circle" title="Interpretação do Tema">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
								</div>
								<span class="gpg-pipeline-label">INTERP</span>
							</div>
							<div class="gpg-pipeline-line"></div>
							<div class="gpg-pipeline-step step-pending" data-step="SEG">
								<div class="gpg-pipeline-circle" title="Segurança & Estrutura">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
								</div>
								<span class="gpg-pipeline-label">SEG</span>
							</div>
							<div class="gpg-pipeline-line"></div>
							<div class="gpg-pipeline-step step-pending" data-step="ESCR">
								<div class="gpg-pipeline-circle" title="Escrita do Post">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
								</div>
								<span class="gpg-pipeline-label">ESCR</span>
							</div>
							<div class="gpg-pipeline-line"></div>
							<div class="gpg-pipeline-step step-pending" data-step="REV">
								<div class="gpg-pipeline-circle" title="Revisão de Conteúdo">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
								</div>
								<span class="gpg-pipeline-label">REV</span>
							</div>
							<div class="gpg-pipeline-line"></div>
							<div class="gpg-pipeline-step step-pending" data-step="PROMPT">
								<div class="gpg-pipeline-circle" title="Geração dos Prompts de Imagem">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
								</div>
								<span class="gpg-pipeline-label">PROMPT</span>
							</div>
							<div class="gpg-pipeline-line"></div>
							<div class="gpg-pipeline-step step-pending" data-step="IMG">
								<div class="gpg-pipeline-circle" title="Geração e Crop das Imagens">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
								</div>
								<span class="gpg-pipeline-label">IMG</span>
							</div>
							<div class="gpg-pipeline-line"></div>
							<div class="gpg-pipeline-step step-pending" data-step="PUB">
								<div class="gpg-pipeline-circle" title="Publicação/Gravação no WP">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
								</div>
								<span class="gpg-pipeline-label">PUB</span>
							</div>
							<div class="gpg-pipeline-line"></div>
							<div class="gpg-pipeline-step step-pending" data-step="INLINE">
								<div class="gpg-pipeline-circle" title="Inserção de Imagens e SEO Final">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
								</div>
								<span class="gpg-pipeline-label">INLINE</span>
							</div>
						</div>
					</div>
				</td>
				<td class="col-status">
					<!-- Modo Edição: Badge Simples -->
					<div class="batch-status-initial">
						<span class="batch-status-badge status-waiting" id="batch-status-${batchRowIndex}">Aguardando</span>
					</div>
					<!-- Modo Progresso/Concluído (Oculto inicialmente) -->
					<div class="batch-status-progress-info" style="display: none;">
						<div class="batch-status-badge-row" style="margin-bottom: 6px;">
							<span class="batch-status-badge status-waiting" id="batch-status-badge-${batchRowIndex}">Aguardando</span>
						</div>
						<div class="batch-progress-bar-container">
							<div class="batch-progress-bar-outer">
								<div class="batch-progress-bar-fill" id="batch-progress-bar-${batchRowIndex}" style="width: 0%;"></div>
							</div>
							<span class="batch-progress-pct-val" id="batch-progress-pct-${batchRowIndex}">0%</span>
						</div>
						<div class="batch-meta-info-row">
							<span class="meta-item-label font-bold" id="batch-meta-provider-${batchRowIndex}">-</span>
							<span class="meta-item-divider">•</span>
							<span class="meta-item-label" id="batch-meta-ratio-${batchRowIndex}">16:9</span>
							<span class="meta-item-divider">•</span>
							<span class="meta-item-label" id="batch-meta-time-${batchRowIndex}">-</span>
						</div>
					</div>
				</td>
				<td class="col-actions" style="text-align: right;">
					<div class="batch-actions-wrapper" id="batch-actions-${batchRowIndex}">
						<button type="button" class="gpg-btn-secondary gpg-batch-remove-row cursor-pointer" style="background:#c62828; padding: 6px 12px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
							<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
							Remover
						</button>
					</div>
				</td>
			</tr>
		`;
		$('#gpg-batch-table tbody').append(row);
	}

	addBatchRow();

	$('#gpg-batch-add-row').on('click', function() {
		addBatchRow();
	});

	// Ação de remover linha em modo de edição
	$(document).on('click', '.gpg-batch-remove-row', function() {
		$(this).closest('tr').remove();
	});

	// Atualiza o estado visual de uma etapa específica
	function updatePipelineStep(rowId, stepName, status) {
		const stepEl = $(`#batch-row-${rowId} .gpg-pipeline-step[data-step="${stepName}"]`);
		stepEl.removeClass('step-pending step-active step-success step-error').addClass('step-' + status);
	}

	// Atualiza a barra de progresso da linha
	function updateLineProgress(rowId, pct) {
		$(`#batch-progress-bar-${rowId}`).css('width', `${pct}%`);
		$(`#batch-progress-pct-${rowId}`).text(`${pct}%`);
	}

	// Inicia a simulação inteligente de progresso do texto no frontend
	function startTextGenerationSimulation(rowId) {
		let currentStep = 0;
		// Reseta todas as etapas para pending
		$(`#batch-row-${rowId} .gpg-pipeline-step`).removeClass('step-active step-success step-error').addClass('step-pending');
		updateLineProgress(rowId, 0);

		// Ativa a primeira etapa (INTERP)
		updatePipelineStep(rowId, 'INTERP', 'active');
		updateLineProgress(rowId, 5);

		let timeElapsed = 0;
		clearInterval(simulatedInterval);
		simulatedInterval = setInterval(function() {
			timeElapsed += 1000;
			
			if (currentStep === 0 && timeElapsed >= 4000) {
				updatePipelineStep(rowId, 'INTERP', 'success');
				currentStep = 1;
				updatePipelineStep(rowId, 'SEG', 'active');
				updateLineProgress(rowId, 15);
			} else if (currentStep === 1 && timeElapsed >= 10000) {
				updatePipelineStep(rowId, 'SEG', 'success');
				currentStep = 2;
				updatePipelineStep(rowId, 'ESCR', 'active');
				updateLineProgress(rowId, 35);
			} else if (currentStep === 2 && timeElapsed >= 22000) {
				updatePipelineStep(rowId, 'ESCR', 'success');
				currentStep = 3;
				updatePipelineStep(rowId, 'REV', 'active');
				updateLineProgress(rowId, 50);
			} else if (currentStep === 3 && timeElapsed >= 32000) {
				updatePipelineStep(rowId, 'REV', 'success');
				currentStep = 4;
				updatePipelineStep(rowId, 'PROMPT', 'active');
				updateLineProgress(rowId, 60);
			}
		}, 1000);
	}

	// Conclui a simulação do texto com sucesso
	function finishTextGenerationSimulation(rowId) {
		clearInterval(simulatedInterval);
		updatePipelineStep(rowId, 'INTERP', 'success');
		updatePipelineStep(rowId, 'SEG', 'success');
		updatePipelineStep(rowId, 'ESCR', 'success');
		updatePipelineStep(rowId, 'REV', 'success');
		updatePipelineStep(rowId, 'PROMPT', 'success');
		updateLineProgress(rowId, 65);
	}

	// Ação do Botão Pausar na Linha
	$(document).on('click', '.btn-action-pause', function() {
		if (activeXHR) {
			activeXHR.abort();
		}
		clearInterval(simulatedInterval);
		isBatchPaused = true;

		const rowId = $(this).closest('tr').data('row-id');
		$(`#batch-status-badge-${rowId}`).attr('class', 'batch-status-badge status-waiting').text('Pausado');
		
		$(`#batch-actions-${rowId}`).html(`
			<button type="button" class="gpg-btn-action btn-action-view cursor-pointer btn-resume-row" style="background:#fa5b0f; color:#ffffff; border:none !important;" title="Retomar Processamento">
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg>
			</button>
			<button type="button" class="gpg-btn-action btn-action-close cursor-pointer btn-cancel-row" title="Cancelar e Fechar">
				<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
			</button>
		`);

		$('#gpg-batch-process-btn').prop('disabled', false).html(`
			<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
			Retomar Processamento
		`);
	});

	// Retomar a partir do botão da linha ou botão global
	$(document).on('click', '.btn-resume-row', function() {
		isBatchPaused = false;
		$('#gpg-batch-process-btn').click();
	});

	// Ação de Cancelar/Fechar post em andamento ou deletar concluído
	$(document).on('click', '.btn-cancel-row, .btn-action-close', function() {
		const row = $(this).closest('tr');
		const rowId = row.data('row-id');
		const isCompleted = row.hasClass('state-completed');
		const isProcessing = row.hasClass('state-processing');
		const postId = row.data('post-id');

		if (isCompleted && postId) {
			if (confirm('Deseja realmente remover este post da lista e movê-lo para a lixeira do WordPress?')) {
				$.post(ajaxurl, {
					action: 'gpg_delete_post',
					post_id: postId,
					nonce: nonce
				}, function(response) {
					if (response.success) {
						row.remove();
					} else {
						alert('Erro ao excluir post: ' + response.data.message);
						row.remove();
					}
				}).fail(function() {
					alert('Erro de rede ao excluir post do WordPress.');
					row.remove();
				});
			}
		} else {
			// Se estiver ativamente gerando este post, aborta a requisição e passa para o próximo
			if (isProcessing) {
				if (activeXHR) activeXHR.abort();
				clearInterval(simulatedInterval);
				row.remove();
				if (!isBatchPaused) {
					// Pula para o próximo índice da fila
					setTimeout(function() {
						$('#gpg-batch-process-btn').click();
					}, 100);
				}
			} else {
				// Apenas remove a linha da tabela (estava aguardando ou pausado)
				row.remove();
			}
		}
	});

	// Processar Lote
	$('#gpg-batch-process-btn').on('click', function() {
		const rows = $('.batch-data-row');
		
		// Filtra apenas as linhas selecionadas com a checkbox
		const selectedRows = rows.filter(function() {
			return $(this).find('.batch-select-checkbox').is(':checked');
		});

		if (selectedRows.length === 0) {
			alert('Selecione pelo menos um post com a checkbox para processar.');
			return;
		}

		// Validar configurações gerais do lote
		const batchTone = $('#gpg-batch-tone').val();
		const batchLength = $('#gpg-batch-length').val();
		const batchTextProvider = $('#gpg-batch-text-provider').val();
		const batchTextModel = $('#gpg-batch-text-model').val();
		const batchImageProvider = $('#gpg-batch-image-provider').val();
		const batchImageModel = $('#gpg-batch-image-model').val();
		if (!batchTone || !batchLength || !batchTextProvider || !batchTextModel || !batchImageProvider || (batchImageProvider !== 'none' && !batchImageModel)) {
			alert('Preencha todos os campos obrigatórios globais no topo (Provedores, Modelos, Tamanho e Tom).');
			return;
		}

		// Validar inputs apenas nas linhas selecionadas que ainda estão em edição
		let validationError = false;
		selectedRows.each(function() {
			if ($(this).hasClass('state-editing')) {
				const topic = $(this).find('.batch-topic').val();
				const category = $(this).find('.batch-category').val();
				if (!topic || !category) {
					validationError = true;
				}
			}
		});

		if (validationError) {
			alert('Preencha os campos obrigatórios (Tema e Categoria) de todos os posts selecionados.');
			return;
		}

		// Inicializar ou retomar o processamento
		isBatchPaused = false;
		$('#gpg-batch-process-btn').prop('disabled', true).text('Processando Lote...');
		$('#gpg-batch-add-row').prop('disabled', true);
		$('#gpg-batch-progress-box').show();

		const total = selectedRows.length;
		
		// Conta quantos dos selecionados já foram concluídos
		let completed = selectedRows.filter(function() {
			return $(this).hasClass('state-completed');
		}).length;

		function updateProgressBar() {
			const pct = Math.round((completed / total) * 100);
			$('#gpg-batch-progress-text').text(`Processando fila: ${completed} de ${total} posts`);
			$('#gpg-batch-progress-pct').text(`${pct}%`);
			$('#gpg-batch-progress-fill').css('width', `${pct}%`);
		}

		updateProgressBar();

		function processQueue() {
			if (isBatchPaused) {
				return;
			}

			// Localiza o primeiro post selecionado que ainda não está concluído
			let nextRow = null;
			selectedRows.each(function() {
				if (!$(this).hasClass('state-completed') && !$(this).hasClass('state-error')) {
					nextRow = $(this);
					return false; // break loop
				}
			});

			if (!nextRow) {
				// Finalizou a fila selecionada
				$('#gpg-batch-process-btn').prop('disabled', false).html(`
					<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
					Processar Fila e Agendar
				`);
				$('#gpg-batch-add-row').prop('disabled', false);
				alert('Processamento em lote finalizado!');
				return;
			}

			const row = nextRow;
			const rowId = row.data('row-id');
			row.removeClass('state-editing').addClass('state-processing');

			// Captura os dados do post se estava em modo de edição
			let topicOriginal = '';
			let category = '';
			let publishDate = '';

			if (row.find('.batch-topic').length > 0) {
				topicOriginal = row.find('.batch-topic').val();
				category = row.find('.batch-category').val();
				publishDate = row.find('.batch-date').val();

				// Guarda esses valores no cache do elemento da linha
				row.data('topic', topicOriginal);
				row.data('category', category);
				row.data('date', publishDate);
			} else {
				topicOriginal = row.data('topic');
				category = row.data('category');
				publishDate = row.data('date');
			}

			// Configura o layout de progresso na linha
			row.find('.batch-edit-fields').hide();
			row.find('.batch-post-title-text').text(topicOriginal);
			row.find('.batch-progress-fields').show();
			
			row.find('.batch-status-initial').hide();
			row.find('.batch-status-progress-info').show();
			
			// Badge e metadados
			const statusLabel = $(`#batch-status-badge-${rowId}`);
			statusLabel.attr('class', 'batch-status-badge status-loading').text('Gerando texto...');
			
			const textProvider = $('#gpg-batch-text-provider').val();
			const imageProvider = $('#gpg-batch-image-provider').val();

			$(`#batch-meta-provider-${rowId}`).text(textProvider.toUpperCase());
			$(`#batch-meta-ratio-${rowId}`).text(imageProvider === 'none' ? 'Sem imagem' : '16:9');
			$(`#batch-meta-time-${rowId}`).text(new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }));

			// Configura os botões de ação dinâmicos durante o processamento
			$(`#batch-actions-${rowId}`).html(`
				<button type="button" class="gpg-btn-action btn-action-pause cursor-pointer" title="Pausar Processamento">
					<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
				</button>
				<button type="button" class="gpg-btn-action btn-action-close cursor-pointer" title="Cancelar e Fechar">
					<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
				</button>
			`);

			// Inicia esqueleto de simulação do pipeline
			startTextGenerationSimulation(rowId);

			const textModel = $('#gpg-batch-text-model').val();
			const imageModel = $('#gpg-batch-image-model').val();

			// 1. Gerar Texto do Post (Chamada AJAX)
			activeXHR = $.post(ajaxurl, {
				action: 'gpg_generate_post',
				text_provider: textProvider,
				text_model: textModel,
				topic: topicOriginal,
				keywords: '',
				tone: $('#gpg-batch-tone').val(),
				length: $('#gpg-batch-length').val(),
				category: category,
				nonce: nonce
			}, function(textResponse) {
				if (!textResponse.success) {
					clearInterval(simulatedInterval);
					statusLabel.attr('class', 'batch-status-badge status-error').text('Erro no texto');
					updatePipelineStep(rowId, 'INTERP', 'error');
					updatePipelineStep(rowId, 'SEG', 'error');
					updatePipelineStep(rowId, 'ESCR', 'error');
					updatePipelineStep(rowId, 'REV', 'error');
					updatePipelineStep(rowId, 'PROMPT', 'error');
					row.removeClass('state-processing').addClass('state-error');
					completed++;
					updateProgressBar();
					processQueue();
					return;
				}

				const textData = textResponse.data;
				finishTextGenerationSimulation(rowId);

				let img1Src = ''; let img1Type = '';
				let img2Src = ''; let img2Type = '';

				if (imageProvider === 'none') {
					saveBatchPost();
				} else if (imageProvider === 'puter') {
					// Geração Flux grátis (Puter no navegador)
					statusLabel.text('Gerando imagens (Puter)...');
					updatePipelineStep(rowId, 'IMG', 'active');
					updateLineProgress(rowId, 75);

					puter.ai.txt2img({ prompt: textData.image_1_prompt, model: 'gpt-image-2' })
						.then(function(img1) {
							img1Src = img1.src;
							img1Type = 'base64';

							puter.ai.txt2img({ prompt: textData.image_2_prompt, model: 'gpt-image-2' })
								.then(function(img2) {
									img2Src = img2.src;
									img2Type = 'base64';
									updatePipelineStep(rowId, 'IMG', 'success');
									updateLineProgress(rowId, 85);
									saveBatchPost();
								})
								.catch(function() {
									// Erro na Imagem 2, tenta salvar apenas com Imagem 1
									updatePipelineStep(rowId, 'IMG', 'error');
									saveBatchPost();
								});
						})
						.catch(function() {
							// Erro na Imagem 1, tenta salvar sem imagens
							updatePipelineStep(rowId, 'IMG', 'error');
							saveBatchPost();
						});
				} else {
					// Geração no Servidor (DALL-E ou Imagen 4)
					statusLabel.text('Gerando Imagem 1...');
					updatePipelineStep(rowId, 'IMG', 'active');
					updateLineProgress(rowId, 70);

					activeXHR = $.post(ajaxurl, {
						action: 'gpg_generate_image',
						image_provider: imageProvider,
						image_model: imageModel,
						prompt: textData.image_1_prompt,
						nonce: nonce
					}, function(img1Response) {
						if (img1Response.success) {
							img1Src = img1Response.data.source;
							img1Type = img1Response.data.type;
						}

						statusLabel.text('Gerando Imagem 2...');
						updateLineProgress(rowId, 78);

						activeXHR = $.post(ajaxurl, {
							action: 'gpg_generate_image',
							image_provider: imageProvider,
							image_model: imageModel,
							prompt: textData.image_2_prompt,
							nonce: nonce
						}, function(img2Response) {
							if (img2Response.success) {
								img2Src = img2Response.data.source;
								img2Type = img2Response.data.type;
							}
							updatePipelineStep(rowId, 'IMG', 'success');
							updateLineProgress(rowId, 85);
							saveBatchPost();
						}).fail(function() {
							updatePipelineStep(rowId, 'IMG', 'error');
							saveBatchPost();
						});
					}).fail(function() {
						updatePipelineStep(rowId, 'IMG', 'error');
						saveBatchPost();
					});
				}

				function saveBatchPost() {
					statusLabel.text('Salvando no WP...');
					updatePipelineStep(rowId, 'PUB', 'active');
					updatePipelineStep(rowId, 'INLINE', 'active');
					updateLineProgress(rowId, 90);

					activeXHR = $.post(ajaxurl, {
						action: 'gpg_save_post',
						title: textData.title,
						suggested_slug: textData.suggested_slug,
						meta_description: textData.meta_description,
						excerpt: textData.excerpt,
						content: textData.content,
						keywords: textData.focus_keywords,
						category: category,
						publish_date: publishDate,
						image_1_source: img1Src,
						image_1_type: img1Type,
						image_2_source: img2Src,
						image_2_type: img2Type,
						nonce: nonce
					}, function(saveResponse) {
						if (saveResponse.success) {
							statusLabel.attr('class', 'batch-status-badge status-success').text(saveResponse.data.status);
							updatePipelineStep(rowId, 'PUB', 'success');
							updatePipelineStep(rowId, 'INLINE', 'success');
							updateLineProgress(rowId, 100);
							
							row.removeClass('state-processing').addClass('state-completed');
							row.data('post-id', saveResponse.data.post_id);
							
							// Configura botões de Ver e Editar para o post concluído
							$(`#batch-actions-${rowId}`).html(`
								<a href="${saveResponse.data.permalink}" target="_blank" class="gpg-btn-action btn-action-view cursor-pointer" title="Ver Publicação">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
								</a>
								<a href="${saveResponse.data.edit_link}" target="_blank" class="gpg-btn-action btn-action-edit cursor-pointer" title="Editar no WordPress">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
								</a>
								<button type="button" class="gpg-btn-action btn-action-close cursor-pointer" title="Remover post da fila e lixeira">
									<svg class="gpg-svg-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 !important;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
								</button>
							`);
						} else {
							statusLabel.attr('class', 'batch-status-badge status-error').text('Erro ao salvar');
							updatePipelineStep(rowId, 'PUB', 'error');
							updatePipelineStep(rowId, 'INLINE', 'error');
							row.removeClass('state-processing').addClass('state-error');
						}
						completed++;
						updateProgressBar();
						processQueue();
					}).fail(function() {
						statusLabel.attr('class', 'batch-status-badge status-error').text('Falha ao salvar');
						updatePipelineStep(rowId, 'PUB', 'error');
						updatePipelineStep(rowId, 'INLINE', 'error');
						row.removeClass('state-processing').addClass('state-error');
						completed++;
						updateProgressBar();
						processQueue();
					});
				}

			}).fail(function() {
				clearInterval(simulatedInterval);
				statusLabel.attr('class', 'batch-status-badge status-error').text('Falha de texto');
				updatePipelineStep(rowId, 'INTERP', 'error');
				updatePipelineStep(rowId, 'SEG', 'error');
				updatePipelineStep(rowId, 'ESCR', 'error');
				updatePipelineStep(rowId, 'REV', 'error');
				updatePipelineStep(rowId, 'PROMPT', 'error');
				row.removeClass('state-processing').addClass('state-error');
				completed++;
				updateProgressBar();
				processQueue();
			});
		}

		processQueue();
	});

	// --- FUNÇÕES DE VALIDAÇÃO E SANITIZAÇÃO DE SLUG ---
	function sanitizeSlug(text) {
		let slug = text.toLowerCase();
		// Remove acentos e diacríticos
		slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
		// Remove caracteres especiais, mantendo letras, números, espaços e hifens
		slug = slug.replace(/[^a-z0-9 -]/g, '')
				   .replace(/\s+/g, '-') // Espaços para hifens
				   .replace(/-+/g, '-'); // Evita múltiplos hifens seguidos
		
		// Limita a no máximo 75 caracteres
		if (slug.length > 75) {
			slug = slug.substring(0, 75);
		}
		
		// Limpa hifens sobressalentes no início e fim
		slug = slug.replace(/^-+|-+$/g, '');
		return slug;
	}

	function checkSlugKeyword(slug) {
		const keywordsInput = $('#gpg-keywords').val();
		if (!keywordsInput) {
			$('#gpg-slug-warning').hide();
			return;
		}

		// Divide as palavras-chave por vírgula e limpa
		const keywords = keywordsInput.split(',')
			.map(kw => kw.trim().toLowerCase())
			.filter(kw => kw.length > 0);

		if (keywords.length === 0) {
			$('#gpg-slug-warning').hide();
			return;
		}

		// Sanitiza cada palavra-chave no formato de slug para verificar se está no slug final
		let found = false;
		for (let kw of keywords) {
			let kwSlug = kw.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
						   .replace(/[^a-z0-9 -]/g, '')
						   .replace(/\s+/g, '-');
			if (slug.includes(kwSlug)) {
				found = true;
				break;
			}
		}

		if (found) {
			$('#gpg-slug-warning').fadeOut(150);
		} else {
			$('#gpg-slug-warning').fadeIn(150);
		}
	}

	// Listener para o campo de slug (limpeza em tempo real + contagem + validação)
	$(document).on('input', '#gpg-res-slug', function() {
		const rawVal = $(this).val();
		const cleanVal = sanitizeSlug(rawVal);

		if (rawVal !== cleanVal) {
			$(this).val(cleanVal);
		}

		const len = cleanVal.length;
		$('#slug-char-count').text(len);

		if (len > 75) {
			$('#slug-char-count').css('color', 'var(--gpg-error)');
		} else {
			$('#slug-char-count').css('color', 'var(--gpg-text-muted)');
		}

		checkSlugKeyword(cleanVal);
	});

	// Re-validar quando a palavra-chave no formulário for alterada
	$(document).on('input', '#gpg-keywords', function() {
		const slugVal = $('#gpg-res-slug').val();
		if (slugVal) {
			checkSlugKeyword(slugVal);
		}
	});

	// Adicionar dica visual de Ctrl+Clique nos links dentro do editor de preview
	$(document).on('mouseenter', '#gpg-res-content-editor a', function() {
		if (!$(this).attr('title')) {
			$(this).attr('title', 'Pressione Ctrl + Clique para testar este link em uma nova guia');
		}
	});

	// Forçar a abertura do link em nova aba ao usar Ctrl + Clique (ou Cmd + Clique)
	$(document).on('click', '#gpg-res-content-editor a', function(e) {
		if (e.ctrlKey || e.metaKey) {
			e.preventDefault();
			const url = $(this).attr('href');
			if (url && url !== '#') {
				window.open(url, '_blank');
			}
		}
	});
});
