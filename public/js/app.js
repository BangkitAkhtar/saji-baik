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
if(form.dataset.confirmed==='yes'){delete form.dataset.confirmed;return;}
event.preventDefault();
const submitter=event.submitter;
const dialog=document.createElement('dialog');
dialog.className='confirm-dialog';
const heading=document.createElement('h2');heading.textContent='Konfirmasi tindakan';
const message=document.createElement('p');message.textContent=form.dataset.confirm;
const actions=document.createElement('div');actions.className='confirm-dialog-actions';
const cancel=document.createElement('button');cancel.type='button';cancel.className='button outline';cancel.textContent='Kembali';
const confirm=document.createElement('button');confirm.type='button';confirm.className='button';confirm.textContent='Ya, lanjutkan';
actions.append(cancel,confirm);dialog.append(heading,message,actions);document.body.append(dialog);
cancel.addEventListener('click',()=>dialog.close());
confirm.addEventListener('click',()=>{form.dataset.confirmed='yes';dialog.close();if(submitter){form.requestSubmit(submitter);}else{form.requestSubmit();}});
dialog.addEventListener('close',()=>dialog.remove());
dialog.showModal();cancel.focus();
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
