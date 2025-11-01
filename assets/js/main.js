// assets/js/main.js
document.addEventListener('DOMContentLoaded', function(){
    // small UI behaviors: fade in, check notifications via AJAX (simple polling)
    // You can implement fetch('/itac/notify_fetch.php') to get notifications.
  });
  const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let particles = [];
for(let i=0;i<100;i++){
    particles.push({
        x: Math.random()*canvas.width,
        y: Math.random()*canvas.height,
        r: Math.random()*4+1,
        dx: (Math.random()-0.5)*1,
        dy: (Math.random()-0.5)*1
    });
}

function animate(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    for(let p of particles){
        ctx.beginPath();
        ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
        ctx.fillStyle='rgba(59,130,246,0.7)';
        ctx.fill();
        p.x += p.dx;
        p.y += p.dy;
        if(p.x<0 || p.x>canvas.width) p.dx*=-1;
        if(p.y<0 || p.y>canvas.height) p.dy*=-1;
    }
    requestAnimationFrame(animate);
}
animate();

window.addEventListener('resize',()=>{
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});
