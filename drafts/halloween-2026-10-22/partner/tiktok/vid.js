// node vid.js [preview]  → anim-*.mp4 (8 s, 30 fps) oder Vorschau-Frames
const {chromium}=require('playwright');const fs=require('fs');const {execSync}=require('child_process');
const PREV=process.argv[2]==='preview', OUT=process.argv[3]||'/tmp';
(async()=>{const b=await chromium.launch();
for(const f of fs.readdirSync(__dirname).filter(x=>/^anim-.*\.html$/.test(x))){
 const p=await b.newPage({viewport:{width:1080,height:1920}});
 await p.goto('file://'+__dirname+'/'+f,{waitUntil:'networkidle',timeout:60000});
 await p.evaluate(()=>document.fonts.ready);await p.waitForTimeout(500);
 const name=f.replace('.html','');
 const times=PREV?[0.6,2.4,6]:Array.from({length:240},(_,i)=>i/30);
 const dir=OUT+'/'+name;fs.mkdirSync(dir,{recursive:true});
 let i=0;for(const t of times){
  await p.evaluate(t=>document.getAnimations().forEach(a=>{a.pause();a.currentTime=t*1000}),t);
  await p.screenshot({path:`${dir}/${String(i++).padStart(4,'0')}.jpg`,type:'jpeg',quality:92});
 }
 if(!PREV) execSync(`ffmpeg -y -loglevel error -framerate 30 -i ${dir}/%04d.jpg -c:v libx264 -pix_fmt yuv420p -crf 18 -movflags +faststart ${__dirname}/${name}.mp4`);
 await p.close();console.log(name);
}
await b.close();})().catch(e=>{console.error(e.message);process.exit(1)});
