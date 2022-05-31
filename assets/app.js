/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';
import Vue from "vue";
import axios from "axios";
import CategoryTree from "./components/CategoryTree";

new Vue({
    el: '#app',
    data: {
        newCategoryName: null,
        newCategoryParent: {},
        categories: null,
        categoriesLinearStructure: null
    },
    methods: {
        onSubmit: function () {
            const data = {
                name: this.newCategoryName,
                parent: this.newCategoryParent.id ? this.newCategoryParent.id : null
            }
            const config = {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
            axios.post('/categories', data, config).then( response => {
                if (response.data.status === 'error') {
                    alert('Ошикбка, категория существует')
                }
                if (response.data.status === 'ok') {
                    this.categories = response.data.categories
                    this.categoriesLinearStructure = response.data.categoriesLinearStructure
                    this.newCategoryName = null
                }
            }).catch(err => alert('Произошла ошибка'))
        },
        categoriesUpdated: function (data) {
            this.categories = data.categories
            this.categoriesLinearStructure = data.categoriesLinearStructure
        },
    },
    delimiters: ['${', '}$'],
    components: {
        CategoryTree
    },
    mounted() {
        axios.get('/categories')
            .then(response => {
                this.categories = response.data.categories
                this.categoriesLinearStructure = response.data.categoriesLinearStructure
            })
    }
});
