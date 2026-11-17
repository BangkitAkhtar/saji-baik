const adminToggle=document.querySelector('.admin-menu-toggle');
adminToggle?.addEventListener('click',()=>{
const open=document.body.classList.toggle('sidebar-open');
adminToggle.setAttribute('aria-expanded',String(open));
});
document.addEventListener('click',event=>{
if(document.body.classList.contains('sidebar-open')&&!event.target.closest('.admin-sidebar')&&!event.target.closest('.admin-menu-toggle')){
document.body.classList.remove('sidebar-open');adminToggle?.setAttribute('aria-expanded','false');
}
});
document.querySelector('[data-select-all]')?.addEventListener('change',event=>{
document.querySelectorAll('input[name="meal_ids[]"]').forEach(box=>{box.checked=event.target.checked;});
});