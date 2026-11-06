(() => {
 'use strict';
 const form=document.getElementById('demoCardForm'); if(!form)return;
 const name=document.getElementById('demoCardName'),number=document.getElementById('demoCardNumber'),expiry=document.getElementById('demoCardExpiry'),cvc=document.getElementById('demoCardCvc'),error=document.getElementById('demoCardError'),token=document.getElementById('demoCardToken');
 const cards={'4242424242424242':'approved','4111111111111111':'review','4000000000000002':'declined'};
 let submitting=false;
 const digits=value=>value.replace(/\D/g,'');
 function preview(){
  document.getElementById('cardPreviewName').textContent=name.value.trim().toUpperCase()||'YOUR NAME';
  const value=digits(number.value);
  document.getElementById('cardPreviewNumber').textContent=value.length===16?'•••• •••• •••• '+value.slice(-4):'•••• •••• •••• ••••';
  document.getElementById('cardPreviewExpiry').textContent=expiry.value||'MM/YY';
 }
 name.addEventListener('input',preview);
 number.addEventListener('input',()=>{number.value=digits(number.value).slice(0,16).replace(/(.{4})/g,'$1 ').trim();token.value='';preview();});
 expiry.addEventListener('input',()=>{const value=digits(expiry.value).slice(0,4);expiry.value=value.length>2?value.slice(0,2)+'/'+value.slice(2):value;preview();});
 cvc.addEventListener('input',()=>{cvc.value=digits(cvc.value).slice(0,3);});
 document.querySelectorAll('[data-demo-number]').forEach(button=>button.addEventListener('click',()=>{number.value=button.dataset.demoNumber.replace(/(.{4})/g,'$1 ').trim();number.removeAttribute('aria-invalid');token.value='';preview();expiry.focus();}));
 form.addEventListener('submit',event=>{
  if(submitting){event.preventDefault();return;}
  error.hidden=true;[name,number,expiry,cvc].forEach(input=>input.removeAttribute('aria-invalid'));
  let field,message;
  const match=/^(0[1-9]|1[0-2])\/(\d{2})$/.exec(expiry.value),now=new Date();
  if(!name.value.trim() || name.value.trim().length<2){field=name;message='Enter a demo cardholder name.';}
  else if(!Object.hasOwn(cards,digits(number.value))){field=number;message='Use one of the three demo card numbers shown below. Real cards are not accepted.';}
  else if(!match || new Date(2000+Number(match[2]),Number(match[1]),1)<=now){field=expiry;message='Enter a valid future expiry date in MM/YY format.';}
  else if(!/^\d{3}$/.test(cvc.value)){field=cvc;message='Enter a 3-digit demo security code.';}
  if(message){event.preventDefault();token.value='';error.textContent=message;error.hidden=false;field.setAttribute('aria-invalid','true');field.focus();return;}
  token.value=cards[digits(number.value)];
  // These fields intentionally have no name attribute: only the selected test token is posted.
  submitting=true;form.querySelector('[type=submit]').disabled=true;form.querySelector('[type=submit]').textContent='Processing test payment…';cvc.value='';
 });
 window.addEventListener('pageshow',()=>{submitting=false;const button=form.querySelector('[type=submit]');button.disabled=false;});
})();
