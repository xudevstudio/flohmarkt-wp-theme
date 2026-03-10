const fs = require('fs');
const targetFile = 'c:/Users/mohda/Local Sites/flohmarkt-troedelmarktde/app/public/wp-content/themes/flohmarkt-blog/n8n-workflow-flohmarkt-blog.json';

const workflow = JSON.parse(fs.readFileSync(targetFile, 'utf8'));

// 1. Fix Pick Article
const pickArticle = workflow.nodes.find(n => n.name === 'Pick Article');
if (pickArticle) {
  pickArticle.parameters.jsCode = `const USED_KEY='usedLinks';
const all=$input.all();
const kw=['flohmarkt','troedelmarkt','trödelmarkt','antik','vintage','sammler','secondhand','schnäppchen','raritäten','upcycling','retro','trödel','markt','flohmarktfunde','antiquität'];
let items=[];
for(const it of all){
  const raw=it.json.data||it.json.body||'';
  const xs=raw.match(/<item>[\\s\\S]*?<\\/item>/g)||[];
  for(const x of xs){
    // FIXED: Escaped brackets inside JSON
    const t1=x.match(/<title><!\\[CDATA\\[([^\\]]+)\\]\\]><\\/title>/);
    const t2=x.match(/<title>([^<]+)<\\/title>/);
    const title=(t1?.[1]||t2?.[1]||'').trim();
    const l1=x.match(/<link>([^<]+)<\\/link>/);
    let link=l1?.[1]||'';
    if (link.includes('url=')) try { link = decodeURIComponent(link.split('url=')[1].split('&')[0]); } catch(e){}
    if (link.includes('news.google.com')) {
      const actualUrl = x.match(/<link\\s+href=["']([^"']+)["']/i);
      if (actualUrl) link = actualUrl[1];
    }
    const d1=x.match(/<description><!\\[CDATA\\[([\\s\\S]*?)\\]\\]><\\/description>/);
    const d2=x.match(/<description>([^<]+)<\\/description>/);
    const desc=(d1?.[1]||d2?.[1]||'').trim();
    const i1=x.match(/<image[^>]*>\\s*<url>([^<]+)<\\/url>\\s*<\\/image>/);
    const i2=x.match(/<img[^>]+src=["']([^"']+)["']/i);
    const i3=x.match(/<media:content[^>]+url=["']([^"']+)["']/i);
    const i4=x.match(/<enclosure[^>]+url=["']([^"']+)["']/i);
    const i5=x.match(/<media:thumbnail[^>]+url=["']([^"']+)["']/i);
    const descImg = desc.match(/<img[^>]+src=["']([^"']+)["']/i);
    const i6=x.match(/<News:Image>([^<]+)<\\/News:Image>/i);
    const i7=x.match(/<news:image>([^<]+)<\\/news:image>/i);
    let rssImage=i3?.[1]||i4?.[1]||i5?.[1]||i1?.[1]||descImg?.[1]||i2?.[1]||i6?.[1]||i7?.[1]||'';
    if (rssImage && !rssImage.startsWith('http')) rssImage = '';
    if(title&&link&&kw.some(k=>title.toLowerCase().includes(k)||desc.toLowerCase().includes(k))) {
      items.push({title, link, desc: desc.replace(/<[^>]+>/g, '').trim(), rssImage});
    }
  }
}
const withImages = items.filter(i => i.rssImage && i.rssImage.length > 10);
const withoutImages = items.filter(i => !i.rssImage || i.rssImage.length <= 10);
items = [...withImages, ...withoutImages];
console.log('Total articles found: ' + items.length + ' (' + withImages.length + ' with images)');
const today=new Date().toISOString().slice(0,10);
let used={};
try{used=JSON.parse($getWorkflowStaticData('global')[USED_KEY]||'{}')}catch(e){}
const cutoff=new Date();cutoff.setDate(cutoff.getDate()-30);
Object.keys(used).forEach(d=>{if(d<cutoff.toISOString().slice(0,10))delete used[d]});
const allUsed=Object.values(used).flat();
let fresh=items.filter(i=>!allUsed.includes(i.link));
const freshWithImg = fresh.filter(i => i.rssImage && i.rssImage.length > 10);
if (freshWithImg.length > 0) fresh = freshWithImg;

// FIXED: Removed fallback image here
if(fresh.length === 0) fresh = items.length > 0 ? items : [{title:'Beste Flohmarkt-Tipps fuer Sammler', link:'https://www.bz-berlin.de/thema/flohmarkt', rssImage:'', desc:'Flohmarkt Tipps'}];

const sel=fresh[Math.floor(Math.random()*fresh.length)];
if(!used[today])used[today]=[];
used[today].push(sel.link);
$getWorkflowStaticData('global')[USED_KEY]=JSON.stringify(used);
console.log('Selected: ' + sel.title);
console.log('RSS Image: ' + (sel.rssImage || 'NONE'));
return [{json:{topic:sel.title, redirectUrl:sel.link, rssDescription:sel.desc, rssImage:sel.rssImage}}];`;
}

// 2. Fix Build Image List (No fallback image)
const buildImageList = workflow.nodes.find(n => n.name === 'Build Image List');
if (buildImageList) {
  buildImageList.parameters.jsCode = `const scrape = $('Scrape Content').first().json;
const pixabayResponse = $json;

let images = [];

// 1. Get images from Pixabay API 
try {
  const hits = (pixabayResponse.hits) || [];
  for (const hit of hits) {
    const imgUrl = hit.largeImageURL || hit.webformatURL;
    if (imgUrl && imgUrl.length > 10) {
      images.push(imgUrl);
    }
  }
} catch(e) {}

// 2. Also add any good scraped/RSS images
const scraped = scrape.scrapedImages || [];
for (const img of scraped) {
  if (!images.includes(img) && !img.includes('placeholder') && !img.includes('data:image') && img.length > 20 && img.startsWith('http')) {
    images.push(img);
  }
}

// Remove duplicates, max 8
images = [...new Set(images)].slice(0, 8);

// FIXED: hasRealImages based on ACTUAL image availability, NO fallback generic Wikimedia image here
const hasRealImages = images.length > 0;

return [{json: {
  ...scrape,
  scrapedImages: images,
  hasRealImages: hasRealImages
}}];`;
}

// 3. Fix Validate Image URL
const validateImg = workflow.nodes.find(n => n.name === 'Validate Image URL');
if (validateImg) {
  validateImg.parameters.jsCode = `// Validate image URL before attempting download
const images = $json.scrapedImages || [];
const firstImage = images[0] || '';

// If no valid first image, pass through cleanly without a download URL
if (!firstImage || firstImage.length < 10 || !firstImage.startsWith('http')) {
  console.log('No valid image URL for featured image. URL:', firstImage);
  return [{json: {...$json, _downloadUrl: ''}}]; // Empty string so download node fails predictably or skips
}

console.log('Downloading featured image:', firstImage);
return [{json: {...$json, _downloadUrl: firstImage}}];`;
}

// 4. Fix Build Post
const buildPost = workflow.nodes.find(n => n.name === 'Build Post');
if (buildPost) {
  buildPost.parameters.jsCode = `const cfg = $('GPT Prompt').first().json;
let parsed = {};
try {
  const raw = $json.choices[0].message.content;
  const cleaned = raw.replace(/\`\`\`json\\n?/gi, '').replace(/\`\`\`\\n?/g, '').trim();
  parsed = JSON.parse(cleaned);
} catch(e) {
  try {
    const raw = $json.choices[0].message.content;
    const jsonMatch = raw.match(/\\{[\\s\\S]*\\}/);
    if (jsonMatch) parsed = JSON.parse(jsonMatch[0]);
  } catch(e2) {}
}

const postBody = {
  title: parsed.title || cfg.topic,
  content: parsed.articleHTML || '<p>Artikel nicht verfuegbar.</p>',
  status: 'publish',
  categories: [parsed.category_id || 1],
  excerpt: parsed.metaDescription || '',
  meta: { keywords: (parsed.keywords || []).join(', ') }
};

// FIXED: Only set featured_media if we have a valid media ID - NO fallback/default image
if (cfg.featured_media && typeof cfg.featured_media === 'number' && cfg.featured_media > 0) {
  postBody.featured_media = cfg.featured_media;
  console.log('Featured media set to ID:', cfg.featured_media);
} else {
  console.log('No featured image available - post will have no featured image');
  // Explicitly do not set postBody.featured_media
}

return [{json:{ _wpBase: cfg._wpBase || 'https://www.flohmarkt-troedelmarkt.de', _auth: 'Basic RmxvaG1hcmt0OnpERXogY0Z2MSBZa2VVIGtKUHkgcnNCdCBFRXlK', _postBody: postBody }}];`;
}

fs.writeFileSync(targetFile, JSON.stringify(workflow, null, 2));
console.log('Successfully fixed Pick Article escaping and removed all fallback images.');
