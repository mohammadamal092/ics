
const KEY='ics_data_v1';
const SESSION='ics_current_user';

const defaultData={
  users:[{id:1,username:'admin',password:'admin123',nama_lengkap:'Administrator',role:'admin'}],
  kategori:[
    {id:1,nama_kategori:'Alat Tulis'},
    {id:2,nama_kategori:'Elektronik'},
    {id:3,nama_kategori:'Sembako'}
  ],
  barang:[],
  transaksi_masuk:[],
  transaksi_keluar:[]
};

function loadData(){
  const raw=localStorage.getItem(KEY);
  if(!raw){localStorage.setItem(KEY,JSON.stringify(defaultData));return structuredClone(defaultData)}
  try{return JSON.parse(raw)}catch(e){localStorage.setItem(KEY,JSON.stringify(defaultData));return structuredClone(defaultData)}
}
function saveData(d){localStorage.setItem(KEY,JSON.stringify(d))}
function currentUser(){return JSON.parse(localStorage.getItem(SESSION)||'null')}
function setUser(u){localStorage.setItem(SESSION,JSON.stringify(u))}
function logout(){localStorage.removeItem(SESSION);location.href=path('auth/login.html')}
function path(p){return location.origin+basePath()+p}
function basePath(){
  const parts=location.pathname.split('/').filter(Boolean);
  return parts.length && parts[0].toLowerCase().endsWith('.github.io') ? '/'+parts[0]+'/' : (location.pathname.includes('/ICS_GitHub/')?'/ICS_GitHub/':'/');
}
function go(p){location.href=path(p)}
function rupiah(n){return 'Rp '+Number(n||0).toLocaleString('id-ID')}
function esc(v){return String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}
function idNext(arr){return arr.length?Math.max(...arr.map(x=>Number(x.id)||0))+1:1}
function flash(msg,type='success'){const el=document.getElementById('flash');if(el){el.className='alert alert-'+type;el.textContent=msg;el.hidden=false;setTimeout(()=>el.remove(),3500)}}
function requireAuth(){
  if(!currentUser()){go('auth/login.html');return false} return true
}
function requireAdmin(){
  const u=currentUser(); if(!u){go('auth/login.html');return false}
  if(u.role!=='admin'){alert('Akses ditolak. Halaman ini khusus untuk Admin.');go('dashboard.html');return false}
  return true
}
function nav(){
  const u=currentUser(); if(!u)return;
  const role=u.role;
  document.querySelector('#navbar').innerHTML=`
  <nav class="navbar">
    <div class="navbar-brand">📦 Inventory Control System</div>
    <ul class="navbar-menu">
      <li><a href="${path('dashboard.html')}">Dashboard</a></li>
      <li><a href="${path('barang/index.html')}">Barang</a></li>
      <li><a href="${path('kategori/index.html')}">Kategori</a></li>
      <li><a href="${path('transaksi/masuk.html')}">Barang Masuk</a></li>
      <li><a href="${path('transaksi/keluar.html')}">Barang Keluar</a></li>
      <li><a href="${path('transaksi/riwayat.html')}">Riwayat</a></li>
    </ul>
    <div class="navbar-user"><span>${esc(u.nama_lengkap)} (${esc(role)})</span><a href="#" class="btn-logout" onclick="logout();return false">Logout</a></div>
  </nav>`;
}
function shell(title){
  document.title=title+' - Inventory Control System';
  nav();
  document.querySelector('#footer').innerHTML='<footer class="footer">© '+new Date().getFullYear()+' Inventory Control System - Demo HTML/CSS/JavaScript</footer>';
}
function pageInit(){
  const page=document.body.dataset.page;
  if(['login','register'].includes(page)) return;
  if(!requireAuth())return;
  shell(document.body.dataset.title||'Inventory Control System');
  if(page==='dashboard')dashboard();
  if(page==='barang')barangIndex();
  if(page==='barang-form')barangForm();
  if(page==='kategori')kategoriIndex();
  if(page==='kategori-form')kategoriForm();
  if(page==='masuk')transaksiForm('masuk');
  if(page==='keluar')transaksiForm('keluar');
  if(page==='riwayat')riwayat();
}

document.addEventListener('DOMContentLoaded',()=>{
  loadData();
  const page=document.body.dataset.page;
  if(page==='login')login();
  else if(page==='register')register();
  else pageInit();
});

function login(){
  const form=document.getElementById('loginForm');
  if(currentUser()){go('dashboard.html');return}
  form.addEventListener('submit',e=>{
    e.preventDefault();
    const d=loadData(),u=d.users.find(x=>x.username===form.username.value.trim()&&x.password===form.password.value);
    if(!u){flash('Username atau password salah.','error');return}
    setUser({id:u.id,username:u.username,nama_lengkap:u.nama_lengkap,role:u.role});
    go('dashboard.html');
  });
}
function register(){
  const form=document.getElementById('registerForm');
  form.addEventListener('submit',e=>{
    e.preventDefault();
    const username=form.username.value.trim(),nama=form.nama_lengkap.value.trim(),pw=form.password.value,cf=form.confirm_password.value;
    if(!username||!nama||!pw){flash('Semua field wajib diisi.','error');return}
    if(pw!==cf){flash('Konfirmasi password tidak cocok.','error');return}
    if(pw.length<6){flash('Password minimal 6 karakter.','error');return}
    const d=loadData();
    if(d.users.some(x=>x.username.toLowerCase()===username.toLowerCase())){flash('Username sudah digunakan.','error');return}
    d.users.push({id:idNext(d.users),username,password:pw,nama_lengkap:nama,role:'staff'});
    saveData(d); flash('Registrasi berhasil! Silakan login.');form.reset();
  });
}

function dashboard(){
  const d=loadData();
  const total=d.barang.length;
  const nilai=d.barang.reduce((s,b)=>s+(Number(b.harga_beli)||0)*(Number(b.stok)||0),0);
  const today=new Date().toISOString().slice(0,10);
  const masuk=d.transaksi_masuk.filter(x=>x.tanggal.slice(0,10)===today).length;
  const keluar=d.transaksi_keluar.filter(x=>x.tanggal.slice(0,10)===today).length;
  document.getElementById('stats').innerHTML=`
  <div class="stat-card"><span class="stat-label">Total Jenis Barang</span><span class="stat-value">${total}</span></div>
  <div class="stat-card"><span class="stat-label">Estimasi Nilai Stok</span><span class="stat-value">${rupiah(nilai)}</span></div>
  <div class="stat-card"><span class="stat-label">Transaksi Masuk Hari Ini</span><span class="stat-value">${masuk}</span></div>
  <div class="stat-card"><span class="stat-label">Transaksi Keluar Hari Ini</span><span class="stat-value">${keluar}</span></div>`;
  const low=d.barang.filter(b=>Number(b.stok)<=Number(b.stok_minimum)).sort((a,b)=>a.stok-b.stok);
  document.getElementById('lowStock').innerHTML=low.length?low.map(b=>`<tr class="row-warning"><td>${esc(b.kode_barang)}</td><td>${esc(b.nama_barang)}</td><td>${b.stok}</td><td>${b.stok_minimum}</td></tr>`).join(''):'<tr><td colspan="4" class="text-center">Semua stok aman 👍</td></tr>';
}

function kategoriIndex(){
  const d=loadData(), admin=currentUser().role==='admin';
  document.getElementById('addBtn').style.display=admin?'inline-block':'none';
  renderKategori();
}
function renderKategori(){
  const d=loadData(),admin=currentUser().role==='admin';
  document.getElementById('kategoriRows').innerHTML=d.kategori.sort((a,b)=>a.nama_kategori.localeCompare(b.nama_kategori)).map(k=>`
  <tr><td>${k.id}</td><td>${esc(k.nama_kategori)}</td>${admin?`<td><a class="btn btn-small btn-edit" href="${path('kategori/edit.html?id='+k.id)}">Edit</a><button class="btn btn-small btn-delete" onclick="deleteKategori(${k.id})">Hapus</button></td>`:''}</tr>`).join('')||'<tr><td colspan="3" class="empty">Belum ada kategori.</td></tr>';
}
function deleteKategori(id){
  if(!confirm('Yakin hapus kategori ini?'))return;
  const d=loadData(); if(d.barang.some(b=>Number(b.kategori_id)===id)){alert('Kategori masih digunakan oleh barang.');return}
  d.kategori=d.kategori.filter(x=>x.id!==id);saveData(d);renderKategori();flash('Kategori berhasil dihapus.');
}
function kategoriForm(){
  if(!requireAdmin())return;
  const d=loadData(),form=document.getElementById('kategoriForm'),id=new URLSearchParams(location.search).get('id');
  if(id){const k=d.kategori.find(x=>x.id==id);if(!k){go('kategori/index.html');return}form.nama_kategori.value=k.nama_kategori}
  form.addEventListener('submit',e=>{
    e.preventDefault();const name=form.nama_kategori.value.trim();
    if(!name){flash('Nama kategori wajib diisi.','error');return}
    if(id)d.kategori.find(x=>x.id==id).nama_kategori=name;
    else d.kategori.push({id:idNext(d.kategori),nama_kategori:name});
    saveData(d);go('kategori/index.html');
  });
}

function getBarang(){
  const d=loadData();return d.barang.map(b=>({...b,nama_kategori:d.kategori.find(k=>k.id==b.kategori_id)?.nama_kategori||'-'}));
}
function barangIndex(){
  const admin=currentUser().role==='admin';document.getElementById('addBarang').style.display=admin?'inline-block':'none';
  renderBarang();
  document.getElementById('search').addEventListener('input',renderBarang);
}
function renderBarang(){
  const q=(document.getElementById('search').value||'').toLowerCase(),admin=currentUser().role==='admin';
  const rows=getBarang().filter(b=>(b.nama_barang+' '+b.kode_barang).toLowerCase().includes(q));
  document.getElementById('barangRows').innerHTML=rows.map(b=>`
  <tr class="${Number(b.stok)<=Number(b.stok_minimum)?'row-warning':''}">
  <td>${esc(b.kode_barang)}</td><td>${esc(b.nama_barang)}</td><td>${esc(b.nama_kategori)}</td><td>${esc(b.satuan)}</td>
  <td>${rupiah(b.harga_beli)}</td><td>${rupiah(b.harga_jual)}</td><td>${b.stok}</td>
  ${admin?`<td><a class="btn btn-small btn-edit" href="${path('barang/edit.html?id='+b.id)}">Edit</a><button class="btn btn-small btn-delete" onclick="deleteBarang(${b.id})">Hapus</button></td>`:''}</tr>`).join('')||`<tr><td colspan="${admin?8:7}" class="empty">Belum ada data barang.</td></tr>`;
}
function deleteBarang(id){
  if(!confirm('Yakin hapus barang ini? Semua riwayat transaksi barang ini juga akan dihapus.'))return;
  const d=loadData();d.barang=d.barang.filter(x=>x.id!==id);d.transaksi_masuk=d.transaksi_masuk.filter(x=>x.barang_id!==id);d.transaksi_keluar=d.transaksi_keluar.filter(x=>x.barang_id!==id);saveData(d);renderBarang();flash('Barang berhasil dihapus.');
}
function barangForm(){
  if(!requireAdmin())return;
  const d=loadData(),form=document.getElementById('barangForm'),id=new URLSearchParams(location.search).get('id');
  form.kategori_id.innerHTML='<option value="">-- Pilih Kategori --</option>'+d.kategori.map(k=>`<option value="${k.id}">${esc(k.nama_kategori)}</option>`).join('');
  if(id){
    const b=d.barang.find(x=>x.id==id);if(!b){go('barang/index.html');return}
    form.kode_barang.value=b.kode_barang;form.nama_barang.value=b.nama_barang;form.kategori_id.value=b.kategori_id||'';form.satuan.value=b.satuan;form.harga_beli.value=b.harga_beli;form.harga_jual.value=b.harga_jual;form.stok.value=b.stok;form.stok_minimum.value=b.stok_minimum;
    form.stok.disabled=true;form.stok_help.hidden=false;
  }
  form.addEventListener('submit',e=>{
    e.preventDefault();const kode=form.kode_barang.value.trim(),nama=form.nama_barang.value.trim();
    if(!kode||!nama){flash('Kode dan nama barang wajib diisi.','error');return}
    if(d.barang.some(x=>x.kode_barang.toLowerCase()===kode.toLowerCase()&&String(x.id)!==String(id))){flash('Kode barang sudah digunakan.','error');return}
    if(id){
      const b=d.barang.find(x=>x.id==id);Object.assign(b,{kode_barang:kode,nama_barang:nama,kategori_id:Number(form.kategori_id.value)||null,satuan:form.satuan.value.trim()||'pcs',harga_beli:Number(form.harga_beli.value)||0,harga_jual:Number(form.harga_jual.value)||0,stok_minimum:Number(form.stok_minimum.value)||0});
    }else{
      d.barang.push({id:idNext(d.barang),kode_barang:kode,nama_barang:nama,kategori_id:Number(form.kategori_id.value)||null,satuan:form.satuan.value.trim()||'pcs',harga_beli:Number(form.harga_beli.value)||0,harga_jual:Number(form.harga_jual.value)||0,stok:Number(form.stok.value)||0,stok_minimum:Number(form.stok_minimum.value)||5});
    }
    saveData(d);go('barang/index.html');
  });
}

function transaksiForm(jenis){
  const d=loadData(),form=document.getElementById('transaksiForm');
  form.barang_id.innerHTML='<option value="">-- Pilih Barang --</option>'+d.barang.sort((a,b)=>a.nama_barang.localeCompare(b.nama_barang)).map(b=>`<option value="${b.id}">${esc(b.kode_barang)} - ${esc(b.nama_barang)} (stok: ${b.stok})</option>`).join('');
  form.addEventListener('submit',e=>{
    e.preventDefault();const bid=Number(form.barang_id.value),jumlah=Number(form.jumlah.value),ket=form.keterangan.value.trim(),b=d.barang.find(x=>x.id===bid);
    if(!b||jumlah<=0){flash('Pilih barang dan isi jumlah dengan benar.','error');return}
    if(jenis==='keluar'&&jumlah>b.stok){flash('Stok tidak cukup. Stok tersedia hanya '+b.stok+'.','error');return}
    const rec={id:idNext(d['transaksi_'+jenis]),barang_id:bid,jumlah,keterangan:ket,tanggal:new Date().toISOString(),user_id:currentUser().id,nama_lengkap:currentUser().nama_lengkap};
    d['transaksi_'+jenis].push(rec);b.stok += jenis==='masuk'?jumlah:-jumlah;saveData(d);form.reset();form.barang_id.innerHTML='<option value="">-- Pilih Barang --</option>'+d.barang.map(x=>`<option value="${x.id}">${esc(x.kode_barang)} - ${esc(x.nama_barang)} (stok: ${x.stok})</option>`).join('');flash(jenis==='masuk'?'Barang masuk berhasil dicatat & stok bertambah.':'Barang keluar berhasil dicatat & stok berkurang.');
  });
}
function riwayat(){
  const d=loadData(),all=[...d.transaksi_masuk.map(x=>({...x,jenis:'Masuk'})),...d.transaksi_keluar.map(x=>({...x,jenis:'Keluar'}))].sort((a,b)=>new Date(b.tanggal)-new Date(a.tanggal));
  document.getElementById('riwayatRows').innerHTML=all.map(x=>{const b=d.barang.find(y=>y.id===x.barang_id);return `<tr><td>${new Date(x.tanggal).toLocaleString('id-ID')}</td><td><span class="badge ${x.jenis==='Masuk'?'badge-in':'badge-out'}">${x.jenis}</span></td><td>${esc(b?.kode_barang||'-')}</td><td>${esc(b?.nama_barang||'-')}</td><td>${x.jumlah}</td><td>${esc(x.keterangan||'-')}</td><td>${esc(x.nama_lengkap||d.users.find(u=>u.id===x.user_id)?.nama_lengkap||'-')}</td></tr>`}).join('')||'<tr><td colspan="7" class="empty">Belum ada transaksi.</td></tr>';
}
