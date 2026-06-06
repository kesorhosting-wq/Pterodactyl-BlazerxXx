window.addEventListener('load', function () {
    (() => {
        const defaultLanguage = 'en'; // Fallback default language
        const targetLanguage = localStorage.getItem('selectedLanguage') || defaultLanguage;

        if (targetLanguage === 'en') {
            return;
        }

        const translationCache = new Map();

        // Get custom API URL or default
        const getApiUrl = () => {
            const siteConfig = window.SiteConfiguration || {};
            const customUrl = siteConfig.language_api_url;
            return customUrl && customUrl.trim() !== '' ? customUrl.replace(/\/$/, '') : 'https://api.euphoriadevelopment.uk';
        };

        const fetchProfileData = async () => {
            try {
                const response = await fetch('/extensions/euphoriatheme/admin/languages/settings', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch profile data');
                }

                const data = await response.json();
            } catch (error) {
                console.error('Error fetching profile data:', error);
            }
        };

        const fetchTranslations = async (texts, targetLang) => {
            try {
                if (texts.length === 0) {
                    return {};
                }
        
                const apiUrl = getApiUrl();
                const response = await fetch(`${apiUrl}/translations/translate/bulk`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        texts,
                        targetLang,
                        source: window.location.hostname,
                    }),
                });
        
                const data = await response.json();
                if (data.success) {
                    return data.translations || {};
                } else {
                    console.error('Bulk translation failed:', data.error);
                    return {};
                }
            } catch (error) {
                console.error('Error fetching bulk translations:', error);
                return {};
            }
        };

        const collectTextNodes = () => {
            const textNodes = [];
            const excludedSelectors = [
                '#language-selector-button',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > a:nth-child(4) > div.ServerRow___StyledDiv4-sc-1ibsw91-10.gQExFz',
                'body > footer',
                '#NavigationLink > span',
                '#logo > a > span',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-2.sm\\:gap-4.mb-4 > div.grid.grid-cols-6.gap-2.md\\:gap-4.col-span-4.lg\\:col-span-1.order-last.lg\\:order-none > div:nth-child(3) > div.flex.flex-col.justify-center.overflow-hidden.w-full > div',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-2.sm\\:gap-4.mb-4 > div.grid.grid-cols-6.gap-2.md\\:gap-4.col-span-4.lg\\:col-span-1.order-last.lg\\:order-none > div:nth-child(4) > div.flex.flex-col.justify-center.overflow-hidden.w-full > div',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-2.sm\\:gap-4.mb-4 > div.grid.grid-cols-6.gap-2.md\\:gap-4.col-span-4.lg\\:col-span-1.order-last.lg\\:order-none > div:nth-child(5) > div.flex.flex-col.justify-center.overflow-hidden.w-full > div',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-2.sm\\:gap-4.mb-4 > div.grid.grid-cols-6.gap-2.md\\:gap-4.col-span-4.lg\\:col-span-1.order-last.lg\\:order-none > div:nth-child(6) > div.flex.flex-col.justify-center.overflow-hidden.w-full > div',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-2.sm\\:gap-4.mb-4 > div.grid.grid-cols-6.gap-2.md\\:gap-4.col-span-4.lg\\:col-span-1.order-last.lg\\:order-none > div:nth-child(7) > div.flex.flex-col.justify-center.overflow-hidden.w-full > div',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-2.sm\\:gap-4.mb-4 > div.grid.grid-cols-6.gap-2.md\\:gap-4.col-span-4.lg\\:col-span-1.order-last.lg\\:order-none > div:nth-child(8) > div.flex.flex-col.justify-center.overflow-hidden.w-full > div',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-4.mb-4 > div.col-span-4.sm\\:col-span-2.lg\\:col-span-1.self-end > div > button:nth-child(1)',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-4.mb-4 > div.col-span-4.sm\\:col-span-2.lg\\:col-span-1.self-end > div > button.style-module_4LBM1DKx.style-module_3kBDV_wo.style-module_Yp7-2Fw-.flex-1',
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.grid.grid-cols-4.gap-4.mb-4 > div.col-span-4.sm\\:col-span-2.lg\\:col-span-1.self-end > div > button.style-module_4LBM1DKx.style-module_3kBDV_wo.style-module_2vOYXZWm.flex-1',
                '#language-dropdown > ul > li', 
                '#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.FileEditContainer___StyledDiv3-sc-48rzpu-7.igexuH > div.CodemirrorEditor__EditorContainer-sc-1dzlt6m-0.hWjPIP',
            ];

        // Collect all excluded elements
        const excludedElements = new Set();
        excludedSelectors.forEach((selector) => {
            document.querySelectorAll(selector).forEach((element) => {
                excludedElements.add(element);
                // Add all descendants of the excluded element to the set
                element.querySelectorAll('*').forEach((child) => excludedElements.add(child));
            });
        });

        // Process all elements except those in the excluded set
        const elements = document.querySelectorAll('*:not(script):not(style):not(.no-translate)');
        elements.forEach((element) => {
            if (excludedElements.has(element)) {
                return; // Skip this element and its child nodes
            }

            // Collect text nodes
            element.childNodes.forEach((node) => {
                if (node.nodeType === Node.TEXT_NODE && node.nodeValue.trim() !== '') {
                    textNodes.push(node);
                }
            });
        });

            return textNodes;
        };

        const applyTranslations = (textNodes, translations) => {
            textNodes.forEach((node) => {
                const originalText = node.nodeValue.trim();
                if (translations[originalText]) {
                    node.nodeValue = translations[originalText];
                }
            });
        };

        const translatePage = async () => {
            const textNodes = collectTextNodes();
            const textsToTranslate = textNodes.map((node) => node.nodeValue.trim());

            const textsToFetch = textsToTranslate.filter((text) => !translationCache.has(text));
            const cachedTranslations = Object.fromEntries(
                textsToTranslate.map((text) => [text, translationCache.get(text)]).filter(([, value]) => value)
            );

            const fetchedTranslations = await fetchTranslations(textsToFetch, targetLanguage);

            Object.entries(fetchedTranslations).forEach(([original, translated]) => {
                translationCache.set(original, translated);
            });

            const allTranslations = { ...cachedTranslations, ...fetchedTranslations };
            applyTranslations(textNodes, allTranslations);
        };

        const observeDOMChanges = () => {
            let timeoutId;

            const observer = new MutationObserver(() => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }

                timeoutId = setTimeout(() => {
                    translatePage();
                }, 300); // Throttle DOM changes to every 300ms
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });
        };
        
        const initialize = async () => {
            await fetchProfileData();
            await translatePage();
        };
        
        // Initialize and observe DOM changes
        initialize();
        observeDOMChanges();
    })();
});