document.addEventListener('DOMContentLoaded', () => {
 const form=document.getElementById('checkoutForm'), message=document.getElementById('checkoutMessage'), button=document.getElementById('placeOrderButton');
 const cart=readShopCart(); const money=n=>new Intl.NumberFormat('en-ZA',{style:'currency',currency:'ZAR'}).format(n);
 const container=document.getElementById('checkoutProducts'); let total=0;
 for (const item of cart) { const row=document.createElement('p'); row.textContent=`${item.name} × ${item.quantity} — ${money(item.price*item.quantity)}`; container.append(row);total+=item.price*item.quantity; }
 for (const id of ['checkoutSubtotal','checkoutTotal']) document.getElementById(id).textContent=money(total);
 document.getElementById('eftDetails').style.display='block';
 if (!cart.length) { message.textContent='Your cart is empty. Add products from the shop before ordering.';button.disabled=true; }
 form.addEventListener('submit', async event => {
  event.preventDefault(); if (!form.reportValidity()) return;
  button.disabled=true; button.textContent='SAVING YOUR ORDER…'; message.textContent='Saving your order. Please wait…';message.classList.remove('checkout-feedback-error');
  const controller=new AbortController();const timeout=setTimeout(()=>controller.abort(),20000);
  const body=new FormData(form); body.set('cart',JSON.stringify(readShopCart()));
  try { const response=await fetch(form.action,{method:'POST',body,signal:controller.signal});
   if (!response.headers.get('content-type')?.includes('application/json')) throw Error('Your session expired. Sign in and return to checkout.');
   const data=await response.json(); if (!response.ok || !data.order_id) throw Error(data.error || 'Unable to place order.');
   try {localStorage.removeItem('teCrownCart');} catch (_) {} window.location.href='demo-payment.php?id='+encodeURIComponent(data.order_id);
  } catch (error) { message.textContent=error.name==='AbortError'?'This is taking longer than expected. Your details are still here. Retry once; the same order reference prevents a duplicate.':error.message;message.classList.add('checkout-feedback-error');message.focus();message.scrollIntoView({behavior:'smooth',block:'center'});button.disabled=false;button.textContent='RETRY PLACE ORDER'; } finally {clearTimeout(timeout);}
 });
});
