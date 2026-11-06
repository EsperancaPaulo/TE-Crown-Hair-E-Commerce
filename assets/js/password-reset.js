(() => {
 const form=document.getElementById('passwordResetForm');if(!form)return;
 const notice=document.getElementById('resetLinkNotice');
 const input=document.getElementById('resetToken');
 const token=new URLSearchParams(location.hash.slice(1)).get('token') || input.value;
 // Remove the secret from the address bar; never store it in browser storage or history state.
 history.replaceState(null,'',location.pathname);
 if(token && /^[a-f0-9]{64}$/.test(token)) {
  input.value=token;
  document.getElementById('resetFields').disabled=false;
  notice.textContent='Your reset link is ready. Enter your new password below.';
 }
})();
