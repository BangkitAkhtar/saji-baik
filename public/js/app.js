document.querySelector('.menu-toggle')?.addEventListener('click',function(){
const expanded=this.getAttribute('aria-expanded')==='true';
this.setAttribute('aria-expanded',String(!expanded));
document.querySelector('.site-header').classList.toggle('nav-open',!expanded);
});
document.querySelectorAll('[data-password]').forEach(button=>button.addEventListener('click',()=>{
const input=document.getElementById(button.dataset.password);
input.type=input.type==='password'?'text':'password';
button.textContent=input.type==='password'?'Lihat':'Sembunyikan';
button.setAttribute('aria-label',input.type==='password'?'Tampilkan kata sandi':'Sembunyikan kata sandi');
}));
document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{
if(!window.confirm(form.dataset.confirm))event.preventDefault();
}));
const checkout=document.querySelector('[data-checkout]');
if(checkout){
const quantity=checkout.querySelector('[name="quantity"]');
const update=()=>{const total='Rp'+(Number(checkout.dataset.price)*Number(quantity.value)).toLocaleString('id-ID');checkout.querySelector('[data-subtotal]').textContent=total;checkout.querySelector('[data-total]').textContent=total;};
quantity.addEventListener('change',update);update();
}
document.querySelectorAll('[data-copy]').forEach(button=>button.addEventListener('click',async()=>{
try{await navigator.clipboard.writeText(button.dataset.copy);button.textContent='Kode tersalin ✓';}
catch{button.textContent='Kode: '+button.dataset.copy;}
}));
