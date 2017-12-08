(function ($) {
    "use strict";

    var pluginName = 'kbModalAjax';

    /**
     * Retrieves the script tags in document
     * @return {Array}
     */
    var getPageScriptTags = function () {
        var scripts = [];
        jQuery('script[src]').each(function () {
            scripts.push(jQuery(this).attr('src'));
        });
        return scripts;
    };


    /**
     * Retrieves the CSS links in document
     * @return {Array}
     */
    var getPageCssLinks = function () {
        var links = [];
        jQuery('link[rel="stylesheet"]').each(function () {
            links.push(jQuery(this).attr('href'));
        });
        return links;
    };

    function ModalAjax(element, options) {
        this.element = element;
        this.init(options);
    };

    ModalAjax.prototype.init = function (options) {
        this.selector = options.selector || null;
        this.initalRequestUrl = options.url;
        this.ajaxSubmit = options.ajaxSubmit || true;
        jQuery(this.element).on('show.bs.modal', this.shown.bind(this));
    };

    /**
     * Requests the content of the modal and injects it, called after the
     * modal is shown
     */
    ModalAjax.prototype.shown = function () {
        // Clear original html before loading
        jQuery(this.element).find('.modal-body').html('');

        jQuery.ajax({
            url: this.initalRequestUrl,
            context: this,
            beforeSend: function (xhr, settings) {
                jQuery(this.element).triggerHandler('kbModalBeforeShow', [xhr, settings]);
            },
            success: function (data, status, xhr) {
                this.injectHtml(data);
                if (this.ajaxSubmit) {
                    jQuery(this.element).off('submit').on('submit', this.formSubmit.bind(this));
                }
                jQuery(this.element).triggerHandler('kbModalShow', [data, status, xhr, this.selector]);
            }
        });
    };

    /**
     * Injects the form of given html into the modal and extecutes css and js
     * @param  {string} html the html to inject
     */
    ModalAjax.prototype.injectHtml = function (html) {
        // Find form and inject it
        var form = jQuery(html).filter('form');

        // Remove existing forms
        if (jQuery(this.element).find('form').length > 0) {
            jQuery(this.element).find('form').off().yiiActiveForm('destroy').remove();
        }

        jQuery(this.element).find('.modal-body').html(html);

        var knownScripts = getPageScriptTags();
        var knownCssLinks = getPageCssLinks();
        var newScripts = [];
        var inlineInjections = [];
        var loadedScriptsCount = 0;

        // Find some element to append to
        var headTag = jQuery('head');
        if (headTag.length < 1) {
            headTag = jQuery('body');
            if (headTag.length < 1) {
                headTag = jQuery(document);
            }
        }

        // CSS stylesheets that haven't been added need to be loaded
        jQuery(html).filter('link[rel="stylesheet"]').each(function () {
            var href = jQuery(this).attr('href');

            if (knownCssLinks.indexOf(href) < 0) {
                // Append the CSS link to the page
                headTag.append(jQuery(this).prop('outerHTML'));
                // Store the link so its not needed to be requested again
                knownCssLinks.push(href);
            }
        });

        // Scripts that haven't yet been loaded need to be added to the end of the body
        jQuery(html).filter('script').each(function () {
            var src = jQuery(this).attr("src");

            if (typeof src === 'undefined') {
                // If no src supplied, execute the raw JS (need to execute after the script tags have been loaded)
                inlineInjections.push(jQuery(this).text());
            } else if (knownScripts.indexOf(src) < 0) {
                // Prepare src so we can append GET parameter later
                src += (src.indexOf('?') < 0) ? '?' : '&';
                newScripts.push(src);
            }
        });

        /**
         * Scripts loaded callback
         */
        var scriptLoaded = function () {
            loadedScriptsCount += 1;
            if (loadedScriptsCount === newScripts.length) {
                // Execute inline scripts
                for (var i = 0; i < inlineInjections.length; i += 1) {
                    window.eval(inlineInjections[i]);
                }
            }
        };

        // Load each script tag
        for (var i = 0; i < newScripts.length; i += 1) {
            jQuery.getScript(newScripts[i] + (new Date().getTime()), scriptLoaded);
        }
    };

    /**
     * Adds event handlers to the form to check for submit
     */
    ModalAjax.prototype.formSubmit = function () {
        var form = jQuery(this.element).find('form');

        // Convert form to ajax submit
        jQuery.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            context: this,
            beforeSend: function (xhr, settings) {
                jQuery(this.element).triggerHandler('kbModalBeforeSubmit', [xhr, settings]);
            },
            success: function (data, status, xhr) {
                var contentType = xhr.getResponseHeader('content-type') || '';
                if (contentType.indexOf('html') > -1) {
                    // Assume form contains errors if html
                    this.injectHtml(data);
                    status = false;
                }
                jQuery(this.element).triggerHandler('kbModalSubmit', [data, status, xhr, this.selector]);
            }
        });

        return false;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, pluginName)) {
                $.data(this, pluginName, new ModalAjax(this, options));
            } else {
                $.data(this, pluginName).initalRequestUrl = options.url;
                $.data(this, pluginName).selector = options.selector || null;
                //console.log($.data(this, pluginName).initalRequestUrl);
            }
        });
    };
})(jQuery);

/**
 * It use for events for adding url query params
 *  for example:
 *  $('#modalID').on('kbModalBeforeShow', function(event, xhr, settings) {
        settings['url'] = modalUrl(settings['url'], {'foo':21});
    }).modal('show');
 * @param url
 * @param json
 * @returns {*}
 */
function modalUrl(url, json) {
    var mUrl = url;
    mUrl += ((mUrl.indexOf('?') == -1) ? '?' : '&');
    if (json) {
        mUrl += $.param(json);
    }
    return mUrl;
}