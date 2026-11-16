import './bootstrap';

const desktopNavigation = window.matchMedia('(min-width: 1100px)');
const menus = document.querySelectorAll('.product-menu');
function syncNavigation() {
    menus.forEach(menu => { menu.open = desktopNavigation.matches; });
}
syncNavigation();
desktopNavigation.addEventListener('change', syncNavigation);
document.addEventListener('keydown', event => {
    if (event.key !== 'Escape' || desktopNavigation.matches) return;
    menus.forEach(menu => {
        if (menu.open) { menu.open = false; menu.querySelector('summary').focus(); }
    });
});
menus.forEach(menu => {
    menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
        if (!desktopNavigation.matches) menu.open = false;
    }));
});
document.querySelectorAll('[data-open-dialog]').forEach(button => {
    button.addEventListener('click', () => document.getElementById(button.dataset.openDialog).showModal());
});
document.querySelectorAll('[data-close-dialog]').forEach(button => {
    button.addEventListener('click', () => button.closest('dialog').close());
});

document.querySelectorAll('table').forEach(table=>{const wrapper=document.createElement('div');wrapper.className='product-table-scroll';wrapper.tabIndex=0;wrapper.setAttribute('role','region');wrapper.setAttribute('aria-label','Scrollable table');table.before(wrapper);wrapper.append(table);});
document.querySelectorAll('label:not([for])').forEach((label,index)=>{const field=label.parentElement.querySelector('input:not([type=hidden]),select,textarea');if(field){field.id ||= 'field-'+index;label.htmlFor=field.id;}});
