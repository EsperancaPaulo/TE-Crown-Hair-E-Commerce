// Offline behavioural checks for the browser-only validator; no payment data is transmitted.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const code=fs.readFileSync(__dirname+'/../assets/js/demo-payment.js','utf8');
function setup(){
 const elements={};
 const element=id=>elements[id]||=( {value:'',textContent:'',hidden:false,disabled:false,dataset:{},attributes:{},events:{},addEventListener(event,fn){this.events[event]=fn},removeAttribute(n){delete this.attributes[n]},setAttribute(n,v){this.attributes[n]=v},focus(){this.focused=true}});
 const ids=['demoCardForm','demoCardName','demoCardNumber','demoCardExpiry','demoCardCvc','demoCardError','demoCardToken','cardPreviewName','cardPreviewNumber','cardPreviewExpiry'];ids.forEach(element);
 const submit=element('submit');element('demoCardForm').querySelector=()=>submit;
 const choices=['4242424242424242','4111111111111111','4000000000000002'].map(n=>{const e=element(n);e.dataset.demoNumber=n;return e});
 const window={events:{},addEventListener(e,fn){this.events[e]=fn}};
 vm.runInNewContext(code,{document:{getElementById:element,querySelectorAll:()=>choices},window,Date,Object});
 function fill(id,value){element(id).value=value;element(id).events.input?.()}
 function pay(){const event={prevented:false,preventDefault(){this.prevented=true}};element('demoCardForm').events.submit(event);return event;}
 function valid(number='4242424242424242'){fill('demoCardName','Demo Customer');fill('demoCardNumber',number);fill('demoCardExpiry','12/35');fill('demoCardCvc','123')}
 return {elements,element,fill,pay,valid,submit,choices};
}
for(const [number,token] of [['4242424242424242','approved'],['4111111111111111','review'],['4000000000000002','declined']]){
 const t=setup();t.valid(number);assert.equal(t.pay().prevented,false);assert.equal(t.element('demoCardToken').value,token);assert.equal(t.element('demoCardCvc').value,'');assert.equal(t.pay().prevented,true);console.log('PASS: demo token, CVC clearing and duplicate guard '+token);
}
for(const [id,value] of [['demoCardName',' '],['demoCardNumber','1234567812345678'],['demoCardExpiry','13/35'],['demoCardExpiry','01/20'],['demoCardCvc','12']]){
 const t=setup();t.valid();t.fill(id,value);assert.equal(t.pay().prevented,true);assert.equal(t.element('demoCardToken').value,'');assert.equal(t.element(id).attributes['aria-invalid'],'true');console.log('PASS: invalid '+id+' is blocked and announced');
}
const t=setup();t.valid();assert.equal(t.element('cardPreviewNumber').textContent,'•••• •••• •••• 4242');assert.equal(t.element('cardPreviewName').textContent,'DEMO CUSTOMER');t.choices[1].events.click();assert.equal(t.element('demoCardNumber').value,'4111 1111 1111 1111');console.log('PASS: masked live preview and test-card selection');
