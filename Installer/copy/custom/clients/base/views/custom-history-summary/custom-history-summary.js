({
    events: {
        'mouseenter [rel="tooltip"]': 'showTooltip',
        'mouseleave [rel="tooltip"]': 'hideTooltip'
    },

    /**
     * Load activity history
     * @param options
     */
    loadData: function(options) {

        //show progress message
        app.alert.show('loading-history-progress', {
            level: 'info',
            messages: 'Loading history...',
            autoClose: false
        });

        var url = app.api.buildURL(''+this.context.parent.get('module')+'/'+this.context.parent.get('modelId')+'/custom-history-summary');

        app.api.call('read', url, null, {
            success: _.bind(function(data) {
                this.historySummary = data;
                this.render();
                app.alert.dismiss('loading-history-progress');
            }, this)
        });
    },

    /**
     * Tooltip functions
     */
    showTooltip: function(e) {
        this.$(e.currentTarget).tooltip("show");
    },

    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip("hide");
    },

    unbindDom: function() {
        // Unbind all tooltips on page
        var unbindTooltips = _.bind(function(sel) {
            this.$(sel).each(function() {
                $(this).tooltip('destroy');
            });
        }, this);
        unbindTooltips('[rel="tooltip"]');

        app.view.Field.prototype.unbindDom.call(this);
    }
})