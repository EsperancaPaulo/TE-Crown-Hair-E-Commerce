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
  button.disabled=true; message.textContent='Saving your order…';
  const body=new FormData(form); body.set('cart',JSON.stringify(readShopCart()));
  try { const response=await fetch(form.action,{method:'POST',body});
   if (!response.headers.get('content-type')?.includes('application/json')) throw Error('Your session expired. Sign in and return to checkout.');
   const data=await response.json(); if (!response.ok || !data.order_id) throw Error(data.error || 'Unable to place order.');
   try {localStorage.removeItem('teCrownCart');} catch (_) {} window.location.href='demo-payment.php?id='+encodeURIComponent(data.order_id);
  } catch (error) { message.textContent=error.message; button.disabled=false; }
 });
});
