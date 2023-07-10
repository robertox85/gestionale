import $ from 'jquery';
import '../css/app.css';
import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'

import 'flowbite';
import Datepicker from 'flowbite-datepicker/Datepicker';
import 'select2' // ES6 module
import 'select2/dist/css/select2.css' // ES6 module
import 'axios' // ES6 module
import * as Toastr from 'toastr';
// import 'toastr/build/toastr.css'; //You need style and css loader installed and set

// init jquery
document.addEventListener('DOMContentLoaded', function () {
    window.$ = window.jQuery = $;
    Alpine.plugin(persist);
    window.Alpine = Alpine;
    Alpine.start();

    // init axios
    window.axios = require('axios');
});

document.addEventListener('alpine:init', () => {
    Alpine.data('searchResults', () => ({
        query: '',
        searchResults: [],
        showNoResultsMessage: false,
        selectedItems: [],
        newItem: [],
        entity: '',
        performSearch() {
            // if query is less than 3 characters, don't perform search
            if (this.query.length < 3) {
                this.searchResults = [];
                this.showNoResultsMessage = false;
                return;
            }

            if (this.query.trim() === '') {
                this.searchResults = [];
                this.showNoResultsMessage = false;
                return;
            }

            // Esegui la chiamata AJAX per cercare gli utenti corrispondenti nel database
            // Puoi utilizzare axios o fetch per eseguire la chiamata AJAX
            axios.get('/search', {params: {query: this.query, entity: this.entity}})
                .then(response => {
                    console.log(response.data);
                    // Filtra i risultati per escludere gli utenti già selezionati
                    this.searchResults = response.data.filter(user => {
                        return !this.selectedItems.find(selectedItem => selectedItem.id === user.id);
                    });
                    // Verifica se il risultato è vuoto e imposta showNoResultsMessage
                    this.showNoResultsMessage = this.searchResults.length === 0;
                })
                .catch(error => {
                    console.error(error);
                });
        },
        selectItem(item) {
            let query = this.query;
            this.query = item.nome;
            this.searchResults = [];
            this.selectedItems.push(item);

            // reset the query
            this.query = query;
        },
        removeItem(item) {
            if (this.selectedItems != null) this.selectedItems = this.selectedItems.filter(selectedItem => selectedItem.id !== item.id);

        },
        createItem(item) {
            // get data-attribute from the button

            axios.post('/create', {
                nome: this.newItem.nome ? this.newItem.nome : '',
                cognome: this.newItem.cognome ? this.newItem.cognome : '',
                denominazione: this.newItem.denominazione ? this.newItem.denominazione : '',
                tipo_utente: this.newItem.tipo_utente ? this.newItem.tipo_utente : '',
                entity: this.entity,
            })
                .then(response => {
                    if (response.data.error) {
                        //Toastr.error(response.data.error);
                        alert(response.data.message);
                    } else {
                        this.selectedItems.push(response.data);
                    }

                    // get element with aria-modal="true"
                    let modal = document.querySelector('[aria-modal="true"]');
                    let backdrop = document.querySelector('[modal-backdrop]');
                    // close modal
                    if (modal) {
                        modal.classList.remove('show');
                        modal.setAttribute('aria-hidden', 'true');
                        modal.setAttribute('style', 'display: none');
                        backdrop.remove();
                        // remove overflow hidden from body
                        document.body.classList.remove('overflow-hidden');
                        // reset modal
                    }
                })
                .catch(error => {
                    console.error(error);
                }
            );
        },
        init() {
            //this.selectedItems = (this.$el.dataset.selectedItems !== undefined) ? JSON.parse(this.$el.dataset.selectedItems) : [];
            this.selectedItems = (this.$el.dataset.selecteditems !== undefined && this.$el.dataset.selecteditems !== null && this.$el.dataset.selecteditems !== 'null') ? JSON.parse(this.$el.dataset.selecteditems) : [];
            this.entity = this.$el.dataset.entity;

            if (this.entity === 'assistiti' || this.entity === 'controparti') {
                this.newItem.push({
                    nome: '',
                    cognome: '',
                    denominazione: '',
                    tipo_utente: '',
                })
            }
        }
    }));
});



var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');


// Change the icons inside the button based on previous settings
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    themeToggleDarkIcon.classList.add('hidden');
    themeToggleLightIcon.classList.remove('hidden');
} else {
    themeToggleDarkIcon.classList.remove('hidden');
    themeToggleLightIcon.classList.add('hidden');
}


var themeToggleBtn = document.getElementById('theme-toggle');
themeToggleBtn.addEventListener('click', function () {
    // toggle icons inside button
    themeToggleDarkIcon.classList.toggle('hidden');
    themeToggleLightIcon.classList.toggle('hidden');

    // toggle theme
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('color-theme', 'light');
    } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme', 'dark');
    }
});

// Check the initial theme preference and update the icons
if (localStorage.getItem('color-theme') === 'dark' || (!localStorage.getItem('color-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
    themeToggleDarkIcon.classList.add('hidden');
    themeToggleLightIcon.classList.remove('hidden');
} else {
    document.documentElement.classList.remove('dark');
    themeToggleDarkIcon.classList.remove('hidden');
    themeToggleLightIcon.classList.add('hidden');
}

// on click of the button with attribute data-modal-target

var editUserButton = document.querySelectorAll('[data-modal-target="updateUserModal"]');
editUserButton.forEach(button => {
    button.addEventListener('click', () => {
        const target = document.getElementById(button.dataset.modalTarget);
        const userDataAttr = button.dataset.modalData;
        if (userDataAttr) {
            const userData = JSON.parse(userDataAttr);
            const form = target.querySelector('form');
            const status = form.querySelector('#status');
            const role = form.querySelector('#role');
            const inputs = form.querySelectorAll('input')

            inputs.forEach(input => {
                if (userData[input.name] !== undefined) {
                    input.value = userData[input.name];
                }
            });

            // set value if exists
            if (userData.status) {
                status.value = userData.status;
            }

            // set value if exists
            if (userData.role) {
                role.value = userData.role;
            }
        }
    });
});

var deleteUsersButton = document.querySelectorAll('[data-modal-target="deleteUserModal"]');
deleteUsersButton.forEach(button => {
    button.addEventListener('click', () => {
        const target = document.getElementById(button.dataset.modalTarget);
        const userDataAttr = button.dataset.modalData;
        if (userDataAttr) {
            const userData = JSON.parse(userDataAttr);
            const form = target.querySelector('form');
            const inputs = form.querySelectorAll('input')

            inputs.forEach(input => {
                if (userData[input.name] !== undefined) {
                    console.log(input.name, userData[input.name])
                    input.value = userData[input.name];
                }
            });
        }
    });
});

function emptyForm(form) {
    var inputs = form.querySelectorAll('input');
    inputs.forEach(function (input) {
        input.value = '';
    });
}


var closeButtons = document.querySelectorAll('[data-dismiss-target]');
closeButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        // seleziona l'elemento da nascondere
        var target = document.querySelector(this.getAttribute('data-dismiss-target'));

        // nascondi l'elemento
        target.style.display = 'none';
    });
});

// Toggle password visibility
// data-toggle="password"
// data-target="#password"
var togglePassword = document.querySelectorAll('[data-toggle = "password"]');
togglePassword.forEach(function (button) {
    button.addEventListener('click', function () {
        // seleziona l'elemento da nascondere
        var target = document.querySelector(this.getAttribute('data-target'));

        // nascondi l'elemento
        if (target.type === "password") {
            target.type = "text";
        } else {
            target.type = "password";
        }
    });

});

// on #checkbox-all-search click check all checkboxes
var checkboxAllSearch = document.getElementById('checkbox-all-search');
var checkboxes = document.querySelectorAll('input[type="checkbox"]');

var bulkDeleteUsers = document.getElementById('bulk-delete-utenti');
var bulkActivateUsers = document.getElementById('bulk-activate-utenti');
var bulkDeleteUsersButton = document.getElementById('bulk-delete-utenti-button');
var bulkActivateUsersButton = document.getElementById('bulk-activate-utenti-button');
if (checkboxAllSearch) {
    checkboxAllSearch.addEventListener('click', function () {
        // if checked
        if (this.checked) {
            // check all checkboxes
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = true;
                // get data-id and add to bulkDeleteUsers input value as array
                if (checkbox.dataset.id) {
                    bulkDeleteUsers.value += checkbox.dataset.id + ',';
                    bulkActivateUsers.value += checkbox.dataset.id + ',';
                }
            });
            // remove disabled class from #bulk-delete-utenti-button
            bulkDeleteUsersButton.classList.remove('disabled');
            bulkDeleteUsersButton.classList.remove('opacity-20');
            bulkDeleteUsersButton.classList.remove('cursor-not-allowed');

            bulkActivateUsersButton.classList.remove('disabled');
            bulkActivateUsersButton.classList.remove('opacity-20');
            bulkActivateUsersButton.classList.remove('cursor-not-allowed');
        }

        // if unchecked
        if (!this.checked) {
            // uncheck all checkboxes
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = false;
                // get data-id and remove from bulkDeleteUsers
                if (checkbox.dataset.id) {
                    bulkDeleteUsers.value = bulkDeleteUsers.value.replace(checkbox.dataset.id + ',', '');
                    bulkActivateUsers.value = bulkActivateUsers.value.replace(checkbox.dataset.id + ',', '');
                }
            });
            // add disabled class to #bulk-delete-utenti-button
            bulkDeleteUsersButton.classList.add('disabled');
            bulkDeleteUsersButton.classList.add('opacity-20');
            bulkDeleteUsersButton.classList.add('cursor-not-allowed');

            bulkActivateUsersButton.classList.add('disabled');
            bulkActivateUsersButton.classList.add('opacity-20');
            bulkActivateUsersButton.classList.add('cursor-not-allowed');
        }
    });
}

// on checkbox click
checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener('click', function () {
        // if checked
        if (this.checked) {
            // get data-id and add to bulkDeleteUsers input value as array
            if (this.dataset.id) {
                bulkDeleteUsers.value += this.dataset.id + ',';
                bulkActivateUsers.value += this.dataset.id + ',';
            }
        }

        // if unchecked
        if (!this.checked) {
            // get data-id and remove from bulkDeleteUsers
            if (this.dataset.id) {
                bulkDeleteUsers.value = bulkDeleteUsers.value.replace(this.dataset.id + ',', '');
                bulkActivateUsers.value = bulkActivateUsers.value.replace(this.dataset.id + ',', '');
            }
        }

        // if there are no checkboxes checked
        if (bulkDeleteUsers.value === '') {
            // add disabled class to #bulk-delete-utenti-button
            bulkDeleteUsersButton.classList.add('disabled');
            bulkDeleteUsersButton.classList.add('opacity-20');
            bulkDeleteUsersButton.classList.add('cursor-not-allowed');

            bulkActivateUsersButton.classList.add('disabled');
            bulkActivateUsersButton.classList.add('opacity-20');
            bulkActivateUsersButton.classList.add('cursor-not-allowed');
        } else {
            // remove disabled class from #bulk-delete-utenti-button
            bulkDeleteUsersButton.classList.remove('disabled');
            bulkDeleteUsersButton.classList.remove('opacity-20');
            bulkDeleteUsersButton.classList.remove('cursor-not-allowed');

            bulkActivateUsersButton.classList.remove('disabled');
            bulkActivateUsersButton.classList.remove('opacity-20');
            bulkActivateUsersButton.classList.remove('cursor-not-allowed');
        }
    });
});

// #bulk-delete-utenti-button
if (bulkDeleteUsersButton) {
    // if clicked and has class disabled, return false
    bulkDeleteUsersButton.addEventListener('click', function (e) {
        if (this.classList.contains('disabled')) {
            e.preventDefault();
            return false;
        }
    });
}

if (bulkActivateUsersButton) {
    // if clicked and has class disabled, return false
    bulkActivateUsersButton.addEventListener('click', function (e) {
        if (this.classList.contains('disabled')) {
            e.preventDefault();
            return false;
        }
    });
}

// add actions buttons to #utenti table
/*document.addEventListener('DOMContentLoaded', function () {
    new DataTables('#utenti', {
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Italian.json"
        },
    });
});*/
if (document.getElementById('items-per-page')) {
    document.getElementById('items-per-page').addEventListener('change', function () {
        var itemsPerPage = parseInt(this.value);
        // Ricarica la pagina con il nuovo numero di utenti per pagina
        var url = window.location.href; // Ottieni l'URL corrente
        var updatedUrl = updateQueryStringParameter(url, 'limit', itemsPerPage);
        window.location.href = updatedUrl;
    });
}

function updateQueryStringParameter(url, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = url.indexOf('?') !== -1 ? "&" : "?";
    if (url.match(re)) {
        return url.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        return url + separator + key + "=" + value;
    }
}


const datepickerEl = document.querySelectorAll('input[datepicker]');
if (datepickerEl) {
    datepickerEl.forEach(function (element) {
        new Datepicker(element, {
            buttonClass: 'btn btn-primary',
            format: 'dd/mm/yyyy',
            language: 'it',
            autohide: true,
            weekStart: 1,
            zIndex: 9999,
            todayHighlight: true,
            orientation: 'bottom'
        });
    });
}
window.initDatePicker = function () {
    return;
    setTimeout(function () {
        const datepickerEl = document.querySelectorAll('input[datepicker]');
        if (datepickerEl) {
            datepickerEl.forEach(function (element) {
                new Datepicker(element, {
                    buttonClass: 'btn btn-primary',
                    format: 'dd/mm/yyyy',
                    language: 'it',
                    autohide: true,
                    weekStart: 1,
                    zIndex: 9999,
                    todayHighlight: true,
                    orientation: 'bottom',
                    buttons: {
                        clear: true,
                        today: true,
                    }
                });
            });
        }
    }, 150);
}



