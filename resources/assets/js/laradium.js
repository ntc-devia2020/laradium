/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.resizable = require('jquery-resizable-dom');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('interface-builder', require('./components/InterfaceBuilder.vue').default);

Vue.component('crud-field', require('./components/fields/Crud.vue').default);
Vue.component('table-field', require('./components/fields/Table.vue').default);
Vue.component('code-field', require('./components/Fields/Code.vue').default);
Vue.component('form-submit-field', require('./components/fields/FormSubmit.vue').default);
Vue.component('text-field', require('./components/fields/Text.vue').default);
Vue.component('number-field', require('./components/fields/Number.vue').default);
Vue.component('textarea-field', require('./components/fields/Textarea.vue').default);
Vue.component('boolean-field', require('./components/fields/Boolean.vue').default);
Vue.component('tab-field', require('./components/fields/Tab.vue').default);
Vue.component('tabs-field', require('./components/fields/Tabs.vue').default);
Vue.component('hidden-field', require('./components/fields/Hidden.vue').default);
Vue.component('select-field', require('./components/fields/Select.vue').default);
Vue.component('select2-field', require('./components/fields/Select2.vue').default);
Vue.component('svgicon-field', require('./components/fields/SvgIcon.vue').default);
Vue.component('file-field', require('./components/fields/File.vue').default);
Vue.component('email-field', require('./components/fields/Email.vue').default);
Vue.component('password-field', require('./components/fields/Password.vue').default);
Vue.component('radio-field', require('./components/fields/Radio.vue').default);
Vue.component('date-field', require('./components/fields/Date.vue').default);
Vue.component('datetime-field', require('./components/fields/DateTime.vue').default);
Vue.component('time-field', require('./components/fields/Time.vue').default);
Vue.component('wysiwyg-field', require('./components/fields/Wysiwyg.vue').default);
Vue.component('color-field', require('./components/fields/Color.vue').default);
Vue.component('row-field', require('./components/fields/Row.vue').default);
Vue.component('block-field', require('./components/fields/Block.vue').default);
Vue.component('col-field', require('./components/fields/Col.vue').default);
Vue.component('save-buttons-field', require('./components/fields/SaveButtons.vue').default);
Vue.component('language-selector-field', require('./components/fields/LanguageSelect.vue').default);
Vue.component('link-field', require('./components/fields/Link.vue').default);
Vue.component('button-field', require('./components/fields/Button.vue').default);
Vue.component('custom-content-field', require('./components/fields/CustomContent.vue').default);
Vue.component('mapposition-field', require('./components/fields/MapPosition.vue').default);

Vue.component('hasone-field', require('./components/fields/HasOne.vue').default);
Vue.component('hasmany-field', require('./components/fields/HasMany.vue').default);
Vue.component('belongsto-field', require('./components/fields/BelongsTo.vue').default);
Vue.component('belongstomany-field', require('./components/fields/BelongsToMany.vue').default);
Vue.component('morphto-field', require('./components/fields/MorphTo.vue').default);
Vue.component('widgetconstructor-field', require('./components/fields/WidgetConstructor.vue').default);
Vue.component('tree-field', require('./components/fields/Tree.vue').default);
Vue.component('breadcrumbs-field', require('./components/fields/Breadcrumbs.vue').default);
Vue.component('charts-field', require('./components/fields/Charts.vue').default);

Vue.component('select2', require('./components/Select2.vue').default);
Vue.component('draggable', require('vuedraggable'));
Vue.component('vue-menu', require('./components/fields/Menu.vue').default);
Vue.component('menuitems', require('./components/fields/MenuItems.vue').default);
Vue.component('js-tree', require('./components/fields/JsTree.vue').default);
Vue.component('vue-chart', require('./components/Chart.vue').default);
Vue.component('media-manager', require('./components/MediaManager.vue').default);

require('./misc/import-form');

if (typeof window.laradiumFields === 'undefined') {
    window.laradiumFields = {};
}

for (let key in window.laradiumFields) {
    if (window.laradiumFields.hasOwnProperty(key)) {
        Vue.component(key.split(/(?=[A-Z])/).join('').toLowerCase() + '-field', window.laradiumFields[key])
    }
}

// Google maps
import * as VueGoogleMaps from 'vue2-google-maps'
Vue.use(VueGoogleMaps, {
    load: {
        key: Laradium.settings['credentials.google_maps_api_key'],
        libraries: 'places'
    },
});

// Import TinyMCE
import 'tinymce';
import 'tinymce/themes/silver/theme';

import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/media';
import 'tinymce/plugins/code';
import 'tinymce/plugins/table';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';
import 'tinymce/plugins/hr';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/preview';

Vue.component('editor', require('@tinymce/tinymce-vue').default);

Vue.mixin({
    methods: {
        generateReplacementIds(replacement_ids, replacement_id_list_old) {
            let rand_id = Math.random().toString(36).substring(7);
            let replacement_id_list = _.cloneDeep(replacement_id_list_old);

            let lastId = '';
            for (let repId in replacement_id_list) {
                if (!replacement_ids[replacement_id_list[repId]]) {
                    replacement_ids[replacement_id_list[repId]] = Math.random().toString(36).substring(7);
                }

                lastId = replacement_id_list[repId];
            }

            if (lastId) {
                replacement_ids[lastId] = rand_id;
            }

            return {
                id: rand_id,
                replacement_ids: replacement_ids
            };
        }
    },

    computed: {
        fieldAttributes() {
            let attributes = {};

            if (this.field && this.field.attr) {
                for (let key in this.field.attr) {
                    if (this.field.attr.hasOwnProperty(key) && isNaN(parseInt(key))) {
                        attributes[key] = this.field.attr[key];
                    }
                }
            }

            if (this.data && this.data.attr) {
                for (let key in this.data.attr) {
                    if (this.data.attr.hasOwnProperty(key) && isNaN(parseInt(key))) {
                        attributes[key] = this.data.attr[key];
                    }
                }
            }

            return attributes;
        }
    }
});

Vue.directive('tooltip', {
    bind: bsTooltip,
    update: bsTooltip,
    unbind(el, binding) {
        $(el).tooltip('destroy');
    }
});

function bsTooltip(el, binding) {
    let trigger = 'hover';
    if (binding.modifiers.focus || binding.modifiers.hover || binding.modifiers.click) {
        const t = [];
        if (binding.modifiers.focus) t.push('focus');
        if (binding.modifiers.hover) t.push('hover');
        if (binding.modifiers.click) t.push('click');
        trigger = t.join(' ');
    }

    $(el).tooltip({
        title: binding.value,
        placement: binding.arg,
        trigger: trigger,
        html: binding.modifiers.html ? binding.modifiers.html : false
    });
}

export const serverBus = new Vue();

const app = new Vue({
    el: '#crud-form',
    data: {
        selectedPage: false
    },
    mounted() {
        $(document).on('click', '.js-channel-select-btn', function () {
            let channel = $('.js-channel-select').val();
            window.location = '/admin/pages/create?channel=' + channel;
        })
    }
});
