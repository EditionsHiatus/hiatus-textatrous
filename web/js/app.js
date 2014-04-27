/* global Backbone, jQuery, $, _ */

$(document).ready(function(){
    'use strict';

    $.hiatus = $.hiatus || {};
    $.hiatus.TAT = $.hiatus.TAT || {};

    // Because we don't have REST
    Backbone.emulateHTTP = true;
    Backbone.emulateJSON = true;


    $.hiatus.TAT.Sheet = Backbone.Model.extend({
        url: '/sheet',

        defaults: {
            currentVersion: 0
        },

        wordsForIndex: function (index) {
            var self = this;
            var otherVersions = _.reject(self.get('values'),
                function (set, key) {
                    return key == self.attributes.currentVersion;
                });

            return _.pluck(otherVersions, index);
        }
    });    

    $.hiatus.TAT.SheetView = Backbone.View.extend({

        className: 'sheet_view',
        
        initialize: function(){
            _.bindAll(this, 'render', 'on_select_word', 'on_submit');
            this.model.bind('change', this.render);
            this.model.bind('reset', this.render);
        },

        template: _.template($('#sheet-template').html()),
        
        render: function() {

            var view = this;
            var html = $(this.template(this.model.toJSON()));

            $('.hiatus-placeholder', html)
                .popover({
                    html: true,
                    placement: 'top',
                    trigger: 'click',
                    content: function() {
                        var $this   = $(this);
                        var index   = $this.data('index') || 0;
                        var others  = _.map(view.model.wordsForIndex(index),
                            function (word) {
                                return '<a href="#" class="select-word">' + word + '</a>';
                            });

                        others.push('<input type="text" class="new-word" placeholder="nouveau" data-index="' + index + '" />');

                        return others.join('');
                    }
                });

            return $(this.el).html(html);
        },
        
        events: {
            'click .select-word': 'on_select_word',
            // 'click input[type=submit][name=save]': 'on_submit',
            // 'click input[type=submit][name=delete]': 'on_delete',
        },

        on_select_word: function (event) {
            event.preventDefault();
            this.model.set('currentVersion', (this.model.get('currentVersion') + 1) % this.model.get('values').length);
            this.$el.find('.hiatus-placeholder')
                .fadeTo(150, 0.2, function() {
                    $(this).fadeTo(300, 1.0);
                });
        },

        on_submit: function (argument) {
            // body...
        }

    });

    $.hiatus.TAT.SheetCollection = Backbone.Collection.extend({
        model: $.hiatus.TAT.Sheet,
        url: '/sheets'
    });

    $.hiatus.TAT.SHEETS = new $.hiatus.TAT.SheetCollection();

    $.hiatus.TAT.SHEETS.fetch({

        error: function () {

        },

        success: function (collection) {

            var views = {},
                currentView = null;

            // This version
            var first = collection.models[0];
            currentView = views[first.id] = new $.hiatus.TAT.SheetView({
                el: '#sheet-cntainer',
                model: collection.get(first.id)
            });

            views[first.id].render();

            // Bind other versions
            $('.treeview-menu a').click(function(event){
                event.preventDefault();

                if (currentView) {
                    currentView.undelegateEvents();
                }

                var id = $(this).data('id');
                currentView = views[id] = views[id] || new $.hiatus.TAT.SheetView({
                    el: '#sheet-cntainer',
                    model: collection.get(id)
                });
                views[id].delegateEvents();
                views[id].render();
            });

            $(document).delegate('.popover-content input', 'blur', function (event) {
                return;
                // $(this).closest('.popover-content')
                //     .find('a')
                //     .show();
                //     .animate({width: 'auto'});
            });
            $(document).delegate('.popover-content input', 'focus', function (event) {
                return;
                // $(this).closest('.popover-content')
                //     .find('a')
                //     .hide();
                //     .animate({width: 0});
            });
            $(document).delegate('.popover-content input', 'keypress', function (event) {
                if (event.keyCode === 13) {
                    var word  = $(this).val();
                    var index = $(this).data('index');

                    debugger;
                    currentView.model.amend(index, word);
                }
            });

        }

    });

});