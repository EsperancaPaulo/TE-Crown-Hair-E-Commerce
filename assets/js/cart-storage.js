window.readShopCart = function () {
 try {
  const data=JSON.parse(localStorage.getItem('teCrownCart') || '[]');
  if (!Array.isArray(data)) return [];
  const safe = v => String(v ?? '').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  return data.filter(i=>i && Number.isFinite(Number(i.price)) && Number(i.price)>=0 && Number.isInteger(Number(i.quantity)) && Number(i.quantity)>0).map(i=>({...i, name:String(i.name ?? ""), category:String(i.category ?? ""), colour:String(i.colour ?? ""), length:String(i.length ?? ""), image:/^[a-zA-Z0-9_.-]+$/.test(i.image||'') ? i.image : '', price:Number(i.price), quantity:Math.min(999,Number(i.quantity))}));
 } catch (_) { return []; }
};

window.escapeShopHtml = v => String(v ?? '').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
