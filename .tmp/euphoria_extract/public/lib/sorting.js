document.addEventListener('DOMContentLoaded', function () {
    (() => {
        // Function to handle errors and log them to the console
        const handleError = (message, error) => {
            console.error(message, error);
        };

        // Fetch the server order from the backend
        const fetchServerOrder = async () => {
            try {
                const response = await fetch('/extensions/euphoriatheme/server-order', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });

                const data = await response.json();
                if (data.success && data.order) {
                    return data.order;
                }
                return [];
            } catch (error) {
                handleError('Error fetching server order', error);
                return [];
            }
        };

        // Save the server order to the backend
        const saveServerOrder = async (order) => {
            try {
                await fetch('/extensions/euphoriatheme/server-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ order }),
                });
            } catch (error) {
                handleError('Error saving server order', error);
            }
        };

        // Helper function to wait for a specific element to appear in the DOM
        const waitForElement = selector => new Promise(resolve => {
            try {
                const existingElement = document.querySelector(selector);
                if (existingElement) return resolve(existingElement);

                const observer = new MutationObserver((mutations, obs) => {
                    try {
                        const element = document.querySelector(selector);
                        if (element) {
                            obs.disconnect();
                            resolve(element);
                        }
                    } catch (error) {
                        handleError('Error observing element', error);
                        obs.disconnect();
                    }
                });

                observer.observe(document.body, { childList: true, subtree: true });
            } catch (error) {
                handleError('Error waiting for element', error);
            }
        });

        let sortable = null; // This will hold the Sortable instance
        let touchTimeout = null; // For handling touch hold duration
        let touchStartY = 0; // Track initial touch position
        let hasMoved = false; // Track if user has scrolled

        // Function to check if the current device is mobile
        const isMobile = () => {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                   window.innerWidth <= 768;
        };

        // Function to handle touch events for mobile sorting
        const setupMobileTouchHandlers = (container) => {
            let isHoldingForSort = false;
            let targetElement = null;

            container.addEventListener('touchstart', (e) => {
                // Reset state
                hasMoved = false;
                isHoldingForSort = false;
                touchStartY = e.touches[0].clientY;
                // Find the closest server element with href containing "/server/"
                targetElement = e.target.closest('a[href*="/server/"]');

                if (!targetElement) return;

                // Start timeout for 3-second hold
                touchTimeout = setTimeout(() => {
                    if (!hasMoved && targetElement) {
                        isHoldingForSort = true;
                        // Provide haptic feedback if available
                        if (navigator.vibrate) {
                            navigator.vibrate(50);
                        }
                        // Add visual feedback with CSS class
                        targetElement.classList.add('mobile-sorting-ready');
                        
                        console.log('Mobile sorting activated for server:', targetElement.getAttribute('href'));
                    }
                }, 3000); // 3 seconds
            }, { passive: false });

            // Prevent context menu during touch hold for sorting
            container.addEventListener('contextmenu', (e) => {
                if (targetElement && (touchTimeout || isHoldingForSort)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, { passive: false });

            container.addEventListener('touchmove', (e) => {
                const currentY = e.touches[0].clientY;
                const moveDistance = Math.abs(currentY - touchStartY);

                // If moved more than 10px, consider it scrolling
                if (moveDistance > 10) {
                    hasMoved = true;
                    if (touchTimeout) {
                        clearTimeout(touchTimeout);
                        touchTimeout = null;
                    }
                    // Reset visual feedback if not in sort mode
                    if (!isHoldingForSort && targetElement) {
                        targetElement.classList.remove('mobile-sorting-ready');
                    }
                }

                // If in sort mode, prevent scrolling
                if (isHoldingForSort) {
                    e.preventDefault();
                }
            }, { passive: false });

            container.addEventListener('touchend', () => {
                // Clear timeout if touch ends before 3 seconds
                if (touchTimeout) {
                    clearTimeout(touchTimeout);
                    touchTimeout = null;
                }

                // Reset visual feedback
                if (targetElement) {
                    targetElement.classList.remove('mobile-sorting-ready');
                }

                // Reset state
                isHoldingForSort = false;
                targetElement = null;
                hasMoved = false;
            }, { passive: true });
        };

        // Function to apply server order without making it sortable
        const applyServerOrder = async (container) => {
            try {
                const serverOrder = await fetchServerOrder();
                if (serverOrder.length > 0) {
                    // Create a temporary sortable instance just to apply the order, then destroy it
                    const tempSortable = Sortable.create(container, {
                        animation: 0,
                        disabled: true, // Disable interactions
                        dataIdAttr: 'href',
                    });
                    tempSortable.sort(serverOrder);
                    tempSortable.destroy(); // Remove sortable functionality after applying order
                }
            } catch (error) {
                handleError('Error applying server order', error);
            }
        };

        // Initialize sorting logic - targeting server hrefs directly
        const initializeSorting = async () => {
            try {
                console.log('🔄 Starting sortable initialization...');
                
                // Wait for elements with server hrefs to appear
                console.log('⏳ Waiting for server elements...');
                await waitForElement('a[href*="/server/"]');
                console.log('✅ Server elements detected');
                
                // Find the container that holds all the server elements
                const firstServerElement = document.querySelector('a[href*="/server/"]');
                if (!firstServerElement) {
                    throw new Error('No server elements found');
                }
                
                // Find the parent container by traversing up the DOM
                let container = firstServerElement.parentElement;
                while (container && container !== document.body) {
                    // Check if this container has multiple server elements
                    const serverElementsInContainer = container.querySelectorAll('a[href*="/server/"]');
                    if (serverElementsInContainer.length > 1) {
                        console.log(`✅ Container found with ${serverElementsInContainer.length} server elements`);
                        break;
                    }
                    container = container.parentElement;
                }
                
                if (!container || container === document.body) {
                    throw new Error('Could not find suitable container for server elements');
                }

                // Get all server elements in the container
                const serverElements = container.querySelectorAll('a[href*="/server/"]');
                console.log(`📊 Found ${serverElements.length} server elements to sort`);
                
                if (serverElements.length === 0) {
                    throw new Error('No server elements found to sort');
                }

                // Destroy existing sortable instance if it exists
                if (sortable) {
                    console.log('🗑️ Destroying existing sortable instance');
                    sortable.destroy();
                    sortable = null;
                }

                // Fetch the server order from the backend
                const serverOrder = await fetchServerOrder();
                console.log(`📋 Fetched server order: ${serverOrder.length} items`);

                // Enhanced sortable configuration - target server href elements directly
                const sortableConfig = {
                    animation: 200,
                    draggable: 'a[href*="/server/"]', // Only elements with server hrefs are draggable
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    swapThreshold: 0.65,
                    fallbackTolerance: 5,
                    
                    onStart: function(evt) {
                        console.log('🎯 Drag started - Server:', evt.item.getAttribute('href'));
                        // Provide visual feedback
                        if (navigator.vibrate && isMobile()) {
                            navigator.vibrate([50, 50, 50]);
                        }
                    },
                    
                    onEnd: function(evt) {
                        console.log('🎯 Drag ended - Server:', evt.item.getAttribute('href'));
                        
                        // Extract current order by getting all server href elements in order
                        const currentOrder = [];
                        const serverElementsInOrder = container.querySelectorAll('a[href*="/server/"]');
                        
                        serverElementsInOrder.forEach(element => {
                            const href = element.getAttribute('href');
                            if (href && href.includes('/server/')) {
                                currentOrder.push(href);
                            }
                        });
                        
                        console.log('📝 New server order:', currentOrder);
                        
                        // Save the order
                        if (currentOrder.length > 0) {
                            saveServerOrder(currentOrder).then(() => {
                                console.log('💾 Server order saved successfully');
                            }).catch(error => {
                                console.error('❌ Failed to save server order:', error);
                            });
                        }
                        
                        // Clean up classes
                        evt.item.classList.remove('sortable-chosen', 'sortable-drag', 'mobile-sorting-ready');
                    }
                };

                // Add mobile-specific configuration
                if (isMobile()) {
                    console.log('📱 Configuring for mobile device');
                    setupMobileTouchHandlers(container);
                    
                    sortableConfig.delay = 3000;
                    sortableConfig.delayOnTouchOnly = true;
                    sortableConfig.touchStartThreshold = 10;
                    sortableConfig.forceFallback = true;
                    sortableConfig.fallbackClass = 'sortable-drag';
                    sortableConfig.fallbackOnBody = true;
                } else {
                    console.log('🖥️ Configuring for desktop');
                }

                // Create sortable instance
                console.log('🚀 Creating sortable instance on container...');
                sortable = Sortable.create(container, sortableConfig);
                
                if (sortable) {
                    console.log('✅ Sortable instance created successfully');
                } else {
                    throw new Error('Failed to create sortable instance');
                }

                // Apply saved order if it exists
                if (serverOrder.length > 0) {
                    console.log('🔄 Applying saved server order...');
                    
                    // Create a map of current server elements by their href
                    const elementMap = new Map();
                    const currentServerElements = Array.from(container.querySelectorAll('a[href*="/server/"]'));
                    
                    currentServerElements.forEach(element => {
                        const href = element.getAttribute('href');
                        if (href && href.includes('/server/')) {
                            elementMap.set(href, element);
                        }
                    });
                    
                    // Reorder elements based on saved order
                    const orderedElements = [];
                    serverOrder.forEach(href => {
                        if (elementMap.has(href)) {
                            orderedElements.push(elementMap.get(href));
                            elementMap.delete(href);
                        }
                    });
                    
                    // Add any remaining server elements that weren't in the saved order
                    elementMap.forEach(element => {
                        orderedElements.push(element);
                    });
                    
                    // Apply the new order by moving elements
                    orderedElements.forEach(element => {
                        container.appendChild(element);
                    });
                    
                    console.log('✅ Server order applied successfully');
                }
                
                console.log('🎉 Sortable initialization completed successfully');
                
            } catch (error) {
                console.error('❌ Error initializing sortable:', error);
                handleError('Error initializing sortable instance', error);
            }
        };

        // Observe title changes to trigger the sorting logic
        const observeTitleChanges = () => {
            new MutationObserver(mutations => {
                try {
                    if (mutations[0].target.textContent !== 'Dashboard') return;

                    // Set up sortable instance once server elements are loaded
                    const observer = new MutationObserver(mutations => {
                        try {
                            // Check if server elements are now available
                            const serverElements = document.querySelectorAll('a[href*="/server/"]');
                            if (serverElements.length > 0) {
                                observer.disconnect(); // Stop observing after server elements are found
                                console.log(`🎯 Found ${serverElements.length} server elements, initializing sorting...`);
                                initializeSorting(); // Initialize sorting logic
                            }
                        } catch (error) {
                            handleError('Error in server elements observer', error);
                        }
                    });

                    // Observe the document body for server elements to appear
                    observer.observe(document.body, { childList: true, subtree: true });
                    
                    // Also try to initialize immediately if server elements already exist
                    const existingServerElements = document.querySelectorAll('a[href*="/server/"]');
                    if (existingServerElements.length > 0) {
                        observer.disconnect();
                        console.log(`🎯 Server elements already present (${existingServerElements.length}), initializing sorting...`);
                        initializeSorting();
                    }
                } catch (error) {
                    handleError('Error in main MutationObserver', error);
                }
            }).observe(document.querySelector('title'), {
                childList: true,
                characterData: true,
                subtree: true,
            });
        };

        // Start observing title changes
        observeTitleChanges();
    })();
});