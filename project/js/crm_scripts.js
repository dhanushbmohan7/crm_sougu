$(document).ready(function () {
    
     let autoSyncInterval = null;
            let currentSyncSheetUrl = '';
            let currentDefaultStatus = 1; // Fallback
            let isSyncing = false; // Prevent concurrent syncs

    // Check if Bootstrap modal is available
    if(typeof $.fn.modal === 'undefined'){
        console.error("Bootstrap Modal not loaded. Loading dynamically...");
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js';
        document.head.appendChild(script);
    }

    // Check if jQuery UI is loaded
    if(typeof $.ui === 'undefined' || typeof $.ui.sortable === 'undefined'){
        console.error("jQuery UI Sortable not loaded.");
        $('.board-container').html(
            '<div class="alert alert-danger m-3">' +
            '<h4>Error Loading Kanban Board</h4>' +
            '<p>jQuery UI library is not loaded.</p>' +
            '</div>'
        );
        return;
    }


 



     $.ajax({
                url: 'get_sync_config.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.config) {
                        $('#sheetUrl').val(response.config.sheet_url || '');
                        $('#defaultStatus').val(response.config.default_status || 1);
                        $('#autoSync').prop('checked', response.config.auto_sync === 1);

                        if (response.config.auto_sync === 1 && response.config.sheet_url) {
                            currentSyncSheetUrl = response.config.sheet_url;
                            currentDefaultStatus = response.config.default_status || 1;

                            setTimeout(function() {
                                startAutoSync(currentSyncSheetUrl, currentDefaultStatus);
                            }, 3000);

                            console.log('Auto-sync resumed: every 30 seconds');
                            showAlert('Auto-sync is active (every 30 seconds)', 'info');
                        }
                    }
                },
                error: function() {
                    console.error('Failed to load sync config');
                }
            });

    console.log("Initializing kanban board...");

    // Initialize sortable kanban columns
    $(".sortable").sortable({
        connectWith: ".sortable",
        placeholder: "ui-state-highlight",
        tolerance: "pointer",
        forcePlaceholderSize: true,
        cursor: "move",
        revert: 150,
        delay: 150,
        opacity: 0.8,
        zIndex: 9999,
        receive: function(event, ui) {
            // Remove empty class after receiving item
            $(this).removeClass('empty');
            console.log("Item received in column:", $(this).data('status'));
        },
        start: function(event, ui){
            console.log("Drag started: Lead ID", ui.item.data("id"));
            ui.item.addClass("dragging");
            $('.kanban-column').addClass('dragging-active');
        },
        
        stop: function(event, ui){
            console.log("Drag stopped: Lead ID", ui.item.data("id"));
            ui.item.removeClass("dragging");
            $('.kanban-column').removeClass('dragging-active');
        },

        over: function(event, ui){
            var column = $(this).closest('.kanban-column');
            column.addClass('dragover');
            
            // Highlight empty columns more
            if ($(this).hasClass('empty')) {
                $(this).css('background', '#e9ecef');
            }
        },
        
        out: function(event, ui){
            var column = $(this).closest('.kanban-column');
            column.removeClass('dragover');
            
            // Reset empty column background
            if ($(this).hasClass('empty')) {
                $(this).css('background', '#f8f9fa');
            }
        },

        update: function(event, ui){
            // Check if this is the receiving container
            if (ui.sender) {
                console.log("Item moved between columns");
                updateLead(ui.item);
            } else {
                console.log("Item moved within same column");
                updateLead(ui.item);
            }
        }
    }).disableSelection();

    // Initialize priority stars for existing cards
    initializePriorityStars();

    // Double-click to view lead details
    $(document).on('dblclick', '.kanban-card', function(e) {
        if ($(e.target).hasClass('star') || $(e.target).closest('.star').length) {
            return; // Don't trigger modal when clicking stars
        }
        var leadData = $(this).data('lead-data');
        showLeadDetails(leadData);
    });

    // Add Lead Button Click
    $(document).on('click', '.add-lead-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var statusId = $(this).data('status-id');
        $('#add_lead_status_id').val(statusId);
        
        // Initialize stars for add modal
        $('#addLeadModal .star').removeClass('active');
        $('#addLeadModal .star[data-value="3"]').addClass('active');
        $('#add_priority').val(3);
        
        // Show modal using Bootstrap
        var modalElement = document.getElementById('addLeadModal');
        var modal = new bootstrap.Modal(modalElement);
        modal.show();
    });

    // Initialize stars in add modal
    $(document).on('click', '#addLeadModal .star', function() {
        var value = $(this).data('value');
        $('#addLeadModal .star').removeClass('active');
        $(this).prevAll('.star').addBack().addClass('active');
        $('#add_priority').val(value);
    });

    // Add Lead Form Submission
    $('#addLeadForm').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var statusId = $('#add_lead_status_id').val();
        
        $.ajax({
            url: 'add_lead.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#addLeadForm button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Adding...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    // Hide modal
                    var modalElement = document.getElementById('addLeadModal');
                    var modal = bootstrap.Modal.getInstance(modalElement);
                    modal.hide();
                    
                    $('#addLeadForm')[0].reset();
                    
                    // Add new lead to the appropriate column
                    addLeadToColumn(response.lead, statusId);
                    
                    // Show success message
                    showAlert('Lead added successfully!', 'success');
                    location.reload();
                } else {
                    showAlert('Error: ' + response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showAlert('Network error. Please try again.', 'danger');
            },
            complete: function() {
                $('#addLeadForm button[type="submit"]').prop('disabled', false).html('Add Lead');
            }
        });
    });

    // Update lead priority when star is clicked
    $(document).on('click', '.kanban-card .star', function(e) {
        e.stopPropagation();
        
        var card = $(this).closest('.kanban-card');
        var leadId = card.data('id');
        var priority = $(this).data('value');
        
        // Update star display
        $(this).siblings('.star').removeClass('active');
        $(this).prevAll('.star').addBack().addClass('active');
        
        // Update priority background class
        card.removeClass('priority-1 priority-2 priority-3 priority-4 priority-5');
        card.addClass('priority-' + priority);
        
        // Update database
        updateLeadPriority(leadId, priority);
    });

    // Update lead when dragged
    function updateLead(item){
        let lead_id = item.data("id");
        let new_status = item.closest(".kanban-column").data("status-id");
        let parent_container = item.closest(".sortable");

        let order_list = [];
        parent_container.children(".kanban-card").each(function(){
            order_list.push($(this).data("id"));
        });

        item.css("opacity","0.6");
        var spinner = $('<span class="spinner-border spinner-border-sm ms-1" role="status"></span>');
        item.find('strong').append(spinner);

        $.ajax({
            url: "update_status.php", 
            type: "POST",
            dataType: "json",
            data: {
                lead_id: lead_id,
                status_id: new_status,
                order_list: order_list.join(",")
            },
            success: function(res){
                item.css("opacity","1");
                spinner.remove();
                
                if(res.status === "success"){
                    updateColumnCounts();
                    
                    // Remove empty class if column is no longer empty
                    if (parent_container.hasClass('empty') && parent_container.children('.kanban-card').length > 0) {
                        parent_container.removeClass('empty');
                    }
                    
                    // Show success feedback
                    var originalBg = item.css('background-color');
                    item.css('background-color', '#d4edda');
                    setTimeout(function(){
                        item.css('background-color', originalBg);
                    }, 500);
                } else {
                    showAlert("Update failed: " + (res.message || "Unknown error"), 'danger');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr, status, error){
                console.error('Update error:', error);
                item.css("opacity","1");
                spinner.remove();
                showAlert("Network error. Please check your connection.", 'danger');
                setTimeout(function(){
                    location.reload();
                }, 1500);
            }
        });
    }
    
    // Update column counts
    function updateColumnCounts() {
        $('.kanban-column').each(function(){
            let column = $(this);
            let count = column.find('.kanban-card').length;
            column.find('.badge').text(count);
            
            // Update empty column state
            let sortableContainer = column.find('.sortable');
            if (count === 0) {
                sortableContainer.addClass('empty');
            } else {
                sortableContainer.removeClass('empty');
            }
        });
    }
    
    // Initialize priority stars display
    function initializePriorityStars() {
        $('.kanban-card').each(function() {
            var card = $(this);
            var leadData = card.data('lead-data');
            if (leadData && leadData.priority) {
                var priority = leadData.priority;
                card.find('.star').removeClass('active');
                card.find('.star[data-value="' + priority + '"]').prevAll('.star').addBack().addClass('active');
            }
        });
    }
    
    // Show lead details in modal

function showLeadDetails(leadData) {
    currentLeadData = leadData;
    
    // Format next_follow_up date for input field (YYYY-MM-DD)
    var nextFollowUpDate = '';
    if (leadData.next_follow_up) {
        var date = new Date(leadData.next_follow_up);
        nextFollowUpDate = date.toISOString().split('T')[0];
    }
    
    // Format created date for input field
    var createdDate = '';
    if (leadData.created_at) {
        var created = new Date(leadData.created_at);
        createdDate = created.toISOString().split('T')[0];
    }
    
    // Generate star rating HTML
    var starRatingHTML = '';
    for (let i = 1; i <= 5; i++) {
        starRatingHTML += `<i class="bi bi-star-fill ${leadData.priority >= i ? 'text-warning' : 'text-secondary'}"></i>`;
    }
    
    var html = `
        <form id="leadEditForm">
            <input type="hidden" id="lead_id" name="lead_id" value="${leadData.id || ''}">
            
            <div class="row" style="height: calc(100% - 60px); overflow-y: auto; padding: 0 10px;">
                <!-- Left Column -->
                <div class="col-md-6">
                    <!-- Date (NOT EDITABLE) -->
                    <div class="mb-2">
                        <small class="text-muted">Date:</small>
                        <div class="fw-medium">${leadData.created_at ? formatDate(leadData.created_at) : 'N/A'}</div>
                    </div>
                    
                    <!-- Number (Phone) - NOT EDITABLE -->
                  <div class="mb-3">
    <small class="text-muted">Number:</small>
    <div class="fw-medium d-flex align-items-center gap-2">
        ${leadData.phone || 'N/A'}

        ${leadData.phone ? `
            <a href="https://wa.me/${leadData.phone.replace(/\D/g,'')}" 
               target="_blank" 
               class="text-success fs-5"
               title="Chat on WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>
        ` : ''}
    </div>
</div>
                    
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Name:</label>
                        <input type="text" class="form-control-plaintext p-1" id="edit_lead_name" name="lead_name" 
                               value="${leadData.lead_name || ''}" readonly style="font-size: 0.95rem;">
                    </div>
                    
                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Location:</label>
                        <input type="text" class="form-control-plaintext p-1" id="edit_location" name="location" 
                               value="${leadData.location || ''}" readonly style="font-size: 0.95rem;">
                    </div>
                    
                    <!-- Mail Id -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Mail Id:</label>
                        <input type="email" class="form-control-plaintext p-1" id="edit_email" name="email" 
                               value="${leadData.email || ''}" readonly style="font-size: 0.95rem;">
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="col-md-6">
                    <!-- Requirements -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Requirements:</label>
                        <textarea class="form-control-plaintext p-1" id="edit_requirements" name="requirements" 
                                  rows="2" readonly style="font-size: 0.95rem; min-height: 60px;">${leadData.requiremnts || ''}</textarea>
                    </div>
                    
                    <!-- Budget (Amount) -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Budget:</label>
                        <input type="number" class="form-control-plaintext p-1" id="edit_amount" name="amount" 
                               value="${leadData.amount || '0'}" step="0.01" readonly style="font-size: 0.95rem;">
                    </div>
                    
                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Notes:</label>
                        <textarea class="form-control-plaintext p-1" id="edit_notes" name="notes" 
                                  rows="2" readonly style="font-size: 0.95rem; min-height: 60px;">${leadData.notes || ''}</textarea>
                    </div>
                    
                    <!-- Next Followup Date -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Next Followup Date:</label>
                        <input type="date" class="form-control-plaintext p-1" id="edit_next_follow_up" name="next_follow_up" 
                               value="${nextFollowUpDate}" readonly style="font-size: 0.95rem; min-width: 150px;">
                    </div>
                    
                    <!-- Priority (Star Rating) -->
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Priority:</label>
                        <div class="mt-1">
                            <!-- Star Rating Display (view mode) -->
                            <div class="star-rating-display d-inline" id="starRatingDisplay">
                                ${starRatingHTML}
                            </div>
                            <!-- Star Rating Input (edit mode - initially hidden) -->
                            <div class="star-rating-edit d-none" id="starRatingEdit">
                                <input type="hidden" id="edit_priority" name="priority" value="${leadData.priority || 3}">
                                <span class="star-edit ${leadData.priority >= 1 ? 'active' : ''}" data-value="1">
                                    <i class="bi bi-star${leadData.priority >= 1 ? '-fill text-warning' : ' text-secondary'}"></i>
                                </span>
                                <span class="star-edit ${leadData.priority >= 2 ? 'active' : ''}" data-value="2">
                                    <i class="bi bi-star${leadData.priority >= 2 ? '-fill text-warning' : ' text-secondary'}"></i>
                                </span>
                                <span class="star-edit ${leadData.priority >= 3 ? 'active' : ''}" data-value="3">
                                    <i class="bi bi-star${leadData.priority >= 3 ? '-fill text-warning' : ' text-secondary'}"></i>
                                </span>
                                <span class="star-edit ${leadData.priority >= 4 ? 'active' : ''}" data-value="4">
                                    <i class="bi bi-star${leadData.priority >= 4 ? '-fill text-warning' : ' text-secondary'}"></i>
                                </span>
                                <span class="star-edit ${leadData.priority >= 5 ? 'active' : ''}" data-value="5">
                                    <i class="bi bi-star${leadData.priority >= 5 ? '-fill text-warning' : ' text-secondary'}"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Last Updated at bottom - Fixed position -->
            <div class="border-top pt-2 mt-2" style="position: sticky; bottom: 0; background: white;">
                <div class="row">
                    <div class="col-12">
                        <small class="text-danger fw-semibold">Last Updated:</small>
                        <div class="text-danger fw-semibold">${leadData.updated_at ? formatDate(leadData.updated_at) : 'N/A'}</div>
                    </div>
                </div>
            </div>
            
            <!-- Messages -->
            <div class="row mt-2" id="formMessages" style="display: none;">
                <div class="col-12">
                    <div class="alert alert-dismissible py-2" role="alert">
                        <button type="button" class="btn-close btn-sm" onclick="$(this).closest('.alert').parent().parent().fadeOut()"></button>
                        <span class="message-content"></span>
                    </div>
                </div>
            </div>
        </form>
    `;
    
    $('#leadDetailsContent').html(html);
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('leadDetailsModal'));
    modal.show();
    
    // Reset edit button
    $('.edit_btn').html('Edit').removeClass('btn-success editing').addClass('btn-primary');
}

    // Helper function to get status name
    function getStatusName(statusId) {
        var column = $('.kanban-column[data-status-id="' + statusId + '"]');
        if (column.length) {
            return column.find('.kanban-header .header-content span:first').text();
        }
        return 'Unknown';
    }
    
    // Helper function to format date
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            var date = new Date(dateString);
            return date.toLocaleString();
        } catch (e) {
            return dateString;
        }
    }
    
    // Add new lead to column
    function addLeadToColumn(leadData, statusId) {
        var column = $('.kanban-column[data-status-id="' + statusId + '"]');
        var sortableContainer = column.find('.sortable');
        
        // Create lead card HTML
        var priority = leadData.priority || 3;
        var priority_class = 'priority-' + priority;
        var amountHtml = '';
        if (leadData.amount) {
            amountHtml = `<span class="amount-badge"><i class="bi bi-currency-rupee me-1"></i>${parseFloat(leadData.amount).toFixed(2)}</span>`;
        }
        
        var cardHtml = `
            <div class="kanban-card ${priority_class}" data-id="${leadData.id}" data-lead-data='${JSON.stringify(leadData).replace(/'/g, "&#39;")}'>
                <div class="d-flex justify-content-between align-items-start">
                    <strong class="flex-grow-1">${leadData.lead_name}</strong>
                    <div class="d-flex align-items-center gap-2">
                        ${amountHtml}
                        <small class="text-muted">#${leadData.id}</small>
                    </div>
                </div>
                <div class="mt-1">
                    <small><i class="bi bi-telephone me-1"></i>${leadData.phone}</small>
                    ${leadData.email ? '<br><small><i class="bi bi-envelope me-1"></i>' + leadData.email + '</small>' : ''}
                </div>
                <div class="star-rating mt-2">
                    <span class="star ${priority >= 1 ? 'active' : ''}" data-value="1"><i class="bi bi-star-fill"></i></span>
                    <span class="star ${priority >= 2 ? 'active' : ''}" data-value="2"><i class="bi bi-star-fill"></i></span>
                    <span class="star ${priority >= 3 ? 'active' : ''}" data-value="3"><i class="bi bi-star-fill"></i></span>
                    <span class="star ${priority >= 4 ? 'active' : ''}" data-value="4"><i class="bi bi-star-fill"></i></span>
                    <span class="star ${priority >= 5 ? 'active' : ''}" data-value="5"><i class="bi bi-star-fill"></i></span>
                </div>
            </div>
        `;
        
        // Remove empty class
        sortableContainer.removeClass('empty');
        
        // Append card to column
        sortableContainer.append(cardHtml);
        
        // Make the new card sortable
        $(".sortable").sortable("refresh");
        
        // Update column count
        updateColumnCounts();
    }
    
    // Show alert message
    function showAlert(message, type) {
        // Create alert element
        var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 10000; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Add to body
        $('body').append(alertHtml);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 3000);
    }

    // ==============================
    // GOOGLE SHEET SYNC FUNCTIONS
    // ==============================
    
    // Start sync when button is clicked
      $('#startSyncBtn').click(function() {
                var sheetUrl = $('#sheetUrl').val().trim();
                var defaultStatus = $('#defaultStatus').val();
                var autoSyncEnabled = $('#autoSync').is(':checked');

                if (!sheetUrl) {
                    showAlert('Please enter a valid Google Sheet URL', 'warning');
                    return;
                }

                saveSyncConfig(sheetUrl, defaultStatus, autoSyncEnabled, function(success) {
                    if (success) {
                        currentSyncSheetUrl = sheetUrl;
                        currentDefaultStatus = defaultStatus;

                        var configModal = bootstrap.Modal.getInstance(document.getElementById('syncSheetModal'));
                        configModal.hide();

                        performManualSync(sheetUrl, defaultStatus);

                        if (autoSyncEnabled) {
                            startAutoSync(sheetUrl, defaultStatus);
                        } else {
                            stopAutoSync();
                            showAlert('Auto-sync disabled', 'info');
                        }
                    }
                });
            });




     function performManualSync(sheetUrl, defaultStatus) {
                if (isSyncing) {
                    showAlert('Sync is already running, please wait...', 'warning');
                    return;
                }

                isSyncing = true;

                var progressModal = new bootstrap.Modal(document.getElementById('syncProgressModal'));
                progressModal.show();

                $('#syncStatusText').text('Fetching data from Google Sheet...');
                $('#syncProgressBar').css('width', '20%').text('20%');

                $.ajax({
                    url: 'sync_from_google_sheet.php',
                    type: 'POST',
                    data: {
                        sheet_url: sheetUrl,
                        default_status: defaultStatus,
                        auto_sync: 0
                    },
                    dataType: 'json',
                    success: function(response) {
                        isSyncing = false;

                        $('#syncProgressBar').css('width', '100%').text('100%');
                        $('#syncStatusText').text('Processing complete!');

                        setTimeout(function() {
                            progressModal.hide();
                            showSyncResults(response);
                            updateLastSyncInfo();

                            if (response.new_leads > 0) {
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        }, 800);
                    },
                    error: function(xhr, status, error) {
                        isSyncing = false;
                        progressModal.hide();
                        showAlert('Manual sync failed: ' + (error || 'Connection error'), 'danger');
                        console.error('Manual sync error:', xhr.responseText);
                    }
                });
            }


             $('#startSyncBtn').click(function() {
                // Clear any existing auto-sync interval to prevent clashes
                if (autoSyncInterval) {
                    clearInterval(autoSyncInterval);
                    autoSyncInterval = null;
                }
                var sheetUrl = $('#sheetUrl').val().trim();
                var defaultStatus = $('#defaultStatus').val();
                var autoSync = $('#autoSync').is(':checked');
                if (!sheetUrl) {
                    showAlert('Please enter Google Sheet URL', 'warning');
                    return;
                }
                // Save sync configuration
                saveSyncConfig(sheetUrl, defaultStatus, autoSync);
                // Start sync process
                startSyncProcess(sheetUrl, defaultStatus, autoSync);
            });
    
    // Save sync configuration
    function saveSyncConfig(sheetUrl, defaultStatus, autoSync) {
                $.ajax({
                    url: 'save_sync_config.php',
                    type: 'POST',
                    data: {
                        sheet_url: sheetUrl,
                        default_status: defaultStatus,
                        auto_sync: autoSync ? 1 : 0
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status !== 'success') {
                            console.error('Failed to save config:', response.message);
                        }
                    }
                });
            }
    
    // Start sync process
    function startSyncProcess(sheetUrl, defaultStatus, autoSync) {
        currentSyncSheetUrl = sheetUrl;
        
        // Close config modal
        var configModal = bootstrap.Modal.getInstance(document.getElementById('syncSheetModal'));
        configModal.hide();
        
        // Show progress modal
        var progressModal = new bootstrap.Modal(document.getElementById('syncProgressModal'));
        progressModal.show();
        
        // Update progress
        $('#syncStatusText').text('Fetching data from Google Sheet...');
        $('#syncProgressBar').css('width', '10%').text('10%');
        
        // Start sync
        syncFromGoogleSheet(sheetUrl, defaultStatus, autoSync);
    }
    
    // Sync from Google Sheet
    function syncFromGoogleSheet(sheetUrl, defaultStatus, autoSync) {
                $.ajax({
                    url: 'sync_from_google_sheet.php',
                    type: 'POST',
                    data: {
                        sheet_url: sheetUrl,
                        default_status: defaultStatus
                    },
                    dataType: 'json',
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total * 100;
                                $('#syncProgressBar').css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(response) {
                        // Hide progress modal
                        var progressModal = bootstrap.Modal.getInstance(document.getElementById('syncProgressModal'));
                        progressModal.hide();
                        // Show results
                        showSyncResults(response);
                        // Update last sync info
                        updateLastSyncInfo();
                        // Refresh kanban board if new leads were added
                        if (response.new_leads > 0) {
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                        // Start auto-sync if enabled (after manual sync completes)
                        if (autoSync) {
                            startAutoSync(sheetUrl, defaultStatus);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide progress modal
                        var progressModal = bootstrap.Modal.getInstance(document.getElementById('syncProgressModal'));
                        progressModal.hide();
                        showAlert('Sync failed: ' + error, 'danger');
                    }
                });
            }
    
    // Show sync results
    function showSyncResults(response) {
                var resultsHtml = '';

                if (response.status === 'success') {
                    resultsHtml = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i><strong>Sync Completed Successfully!</strong>
                    </div>
                    <div class="sync-stats">
                        <p><i class="bi bi-check-square me-2"></i> <strong>New Leads Added:</strong> ${response.new_leads || 0}</p>
                        <p><i class="bi bi-x-square me-2"></i> <strong>Duplicates Skipped:</strong> ${response.duplicates || 0}</p>
                        <p><i class="bi bi-file-earmark-text me-2"></i> <strong>Total Rows Processed:</strong> ${response.total_rows || 0}</p>
                        <p><i class="bi bi-clock me-2"></i> <strong>Sync Time:</strong> ${response.sync_time || 'Just now'}</p>
                    </div>
                `;

                    if (response.new_leads > 0) {
                        resultsHtml += `<div class="alert alert-info mt-3"><i class="bi bi-info-circle me-2"></i> Page will refresh in 2 seconds to show new leads.</div>`;
                    } else {
                        resultsHtml += `<div class="alert alert-secondary mt-3"><i class="bi bi-info-circle me-2"></i> No new leads — everything is up to date.</div>`;
                    }
                } else {
                    resultsHtml = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i><strong>Sync Failed!</strong><br>${response.message || 'Unknown error'}
                    </div>
                    <div class="mt-3"><strong>Troubleshooting:</strong>
                        <ul class="mt-2">
                            <li>Ensure the sheet is published to web (File → Share → Publish to web → CSV)</li>
                            <li>URL must end with <code>output=csv</code></li>
                            <li>Sheet must be publicly accessible</li>
                            <li>Check column headers match expected fields</li>
                        </ul>
                    </div>
                `;
                }

                $('#syncResultsContent').html(resultsHtml);
                var resultsModal = new bootstrap.Modal(document.getElementById('syncResultsModal'));
                resultsModal.show();
            }

    
    // Start auto-sync
  function startAutoSync(sheetUrl, defaultStatus) {

                if (autoSyncInterval) {
                    clearInterval(autoSyncInterval);
                }
                // Every 30 seconds
                autoSyncInterval = setInterval(function() {
                    autoSyncFromGoogleSheet(sheetUrl, defaultStatus);
                }, 3 * 1000);
                showAlert('Auto-sync enabled (every 30 seconds)', 'success');
            }

            function stopAutoSync() {
                if (autoSyncInterval) {
                    clearInterval(autoSyncInterval);
                    autoSyncInterval = null;
                    console.log('Auto-sync stopped');
                }
            }
    
    // Auto sync (silent, no UI)
    function autoSyncFromGoogleSheet(sheetUrl, defaultStatus) {
                if (isSyncing) {
                    console.log('Auto-sync skipped: already in progress');
                    return;
                }

                isSyncing = true;
                console.log('Running auto-sync at', new Date().toLocaleTimeString());

                $.ajax({
                    url: 'sync_from_google_sheet.php',
                    type: 'POST',
                    data: {
                        sheet_url: sheetUrl,
                        default_status: defaultStatus,
                        auto_sync: 1
                    },
                    dataType: 'json',
                    timeout: 25000,
                    success: function(response) {
                        isSyncing = false;

                        if (response.status === 'success') {
                            updateLastSyncInfo();

                            if (response.new_leads > 0) {
                                showAutoSyncNotification(response.new_leads);
                                setTimeout(function() {
                                    location.reload();
                                }, 3000);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        isSyncing = false;
                        if (status !== 'abort') {
                            console.error('Auto-sync failed:', error);
                        }
                    }
                });
            }
    
    // Show auto-sync notification
   function showAutoSyncNotification(newLeads) {
                var notificationHtml = `
                    <div class="alert alert-success alert-dismissible fade show position-fixed" style="bottom: 20px; right: 20px; z-index: 10000; min-width: 300px;">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        <strong>Auto-sync Complete!</strong> ${newLeads} new leads added from Google Sheet.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('body').append(notificationHtml);
                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }
    // Update last sync info
    function updateLastSyncInfo() {
                $.ajax({
                    url: 'get_last_sync_info.php',
                    type: 'GET',
                    cache: false,
                    success: function(html) {
                        $('.last-sync-info').html(html);
                    }
                });
            }

    updateColumnCounts();

    // Add hover effect
    $(document).on('mouseenter', '.kanban-card', function() {
        if(!$(this).hasClass('dragging')) {
            $(this).css('transform', 'translateY(-2px)');
            $(this).css('box-shadow', '0 4px 8px rgba(0,0,0,0.15)');
        }
    }).on('mouseleave', '.kanban-card', function() {
        $(this).css('transform', 'translateY(0)');
        $(this).css('box-shadow', '0 1px 3px rgba(0,0,0,0.1)');
    });

});





$(document).ready(function() {
    
    // Edit button click handler - Use event delegation
    $(document).on('click', '.edit_btn', function(e) {
        e.preventDefault();
        
        const $this = $(this);
        const isEditing = $this.hasClass('editing');
        
        if (!isEditing) {
            // Enter edit mode
            enterEditMode();
            $this.html('Save').removeClass('btn-primary').addClass('btn-success').addClass('editing');
        } else {
            // Save changes
            saveLeadChanges();
        }
    });
    
    function enterEditMode() {
        // Enable all input fields
        $('#leadEditForm').find('input[type="text"], input[type="email"], input[type="number"], input[type="date"], textarea').each(function() {
            const $this = $(this);
            $this.removeClass('form-control-plaintext').addClass('form-control');
            $this.prop('readonly', false);
            
            // Style date inputs
            if ($this.attr('type') === 'date') {
                $this.css({
                    'background': 'white',
                    'border': '1px solid #dee2e6'
                });
            }
        });
        
        // Switch to star edit mode
        $('#starRatingDisplay').addClass('d-none');
        $('#starRatingEdit').removeClass('d-none');
        
        // Add star click handlers
        $('.star-edit').off('click').on('click', function() {
            const value = parseInt($(this).data('value'));
            $('#edit_priority').val(value);
            
            // Update star display
            $('.star-edit').each(function() {
                const starValue = parseInt($(this).data('value'));
                const $icon = $(this).find('i');
                
                if (starValue <= value) {
                    $(this).addClass('active');
                    $icon.removeClass('bi-star text-secondary').addClass('bi-star-fill text-warning');
                } else {
                    $(this).removeClass('active');
                    $icon.removeClass('bi-star-fill text-warning').addClass('bi-star text-secondary');
                }
            });
        });
    }
    
    function exitEditMode() {
        // Disable all input fields
        $('#leadEditForm').find('input[type="text"], input[type="email"], input[type="number"], input[type="date"], textarea').each(function() {
            const $this = $(this);
            $this.removeClass('form-control').addClass('form-control-plaintext');
            $this.prop('readonly', true);
            
            // Reset date input styling
            if ($this.attr('type') === 'date') {
                $this.css({
                    'background': 'transparent',
                    'border': 'none'
                });
            }
        });
        
        // Switch back to star display
        $('#starRatingEdit').addClass('d-none');
        $('#starRatingDisplay').removeClass('d-none');
    }
    
    function saveLeadChanges() {
        const $editBtn = $('.edit_btn');
        const formData = $('#leadEditForm').serialize();
        
        // Show loading
        $editBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        
        $.ajax({
            url: 'update_lead.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Show success message
                    showMessage('Lead updated successfully!', 'success');
                    
                    // Update current data
                    if (response.lead) {
                        currentLeadData = response.lead;
                        
                        // Update star display with new priority
                        let newStars = '';
                        for (let i = 1; i <= 5; i++) {
                            newStars += `<i class="bi bi-star-fill ${response.lead.priority >= i ? 'text-warning' : 'text-secondary'}"></i>`;
                        }
                        $('#starRatingDisplay').html(newStars);
                        
                        // Update Last Updated
                        //$('.border-top .text-danger.fw-semibold').last().html(formatDate(response.lead.updated_at));
                    }
                    
                    // Exit edit mode
                    exitEditMode();
                    location.reload();
                    // Reset button
                    $editBtn.html('Edit').removeClass('btn-success editing').addClass('btn-primary');
                    
                } else {
                    showMessage(response.message || 'Error updating lead', 'danger');
                    $editBtn.html('Save').removeClass('btn-primary').addClass('btn-success');
                }
            },
            error: function(xhr, status, error) {
                showMessage('Error saving changes: ' + error, 'danger');
                $editBtn.html('Save').removeClass('btn-primary').addClass('btn-success');
            }
        });
    }
    
    function showMessage(message, type) {
        const $alert = $('#formMessages .alert');
        $alert.removeClass('alert-success alert-danger alert-warning')
              .addClass('alert-' + type)
              .find('.message-content').html(message);
        $('#formMessages').fadeIn();
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            $('#formMessages').fadeOut();
        }, 3000);
    }
    
    // Reset when modal closes
    $('#leadDetailsModal').on('hidden.bs.modal', function() {
        $('.edit_btn').html('Edit').removeClass('btn-success editing').addClass('btn-primary');
        $('#formMessages').hide();
    });
});