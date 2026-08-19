'use strict';
document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('weather-icon');
  if (!canvas || typeof Skycons === 'undefined') return;
  const icon = canvas.dataset.icon;
  if (!icon || !Object.prototype.hasOwnProperty.call(Skycons, icon)) return;
  const skycons = new Skycons({monochrome: false, colors: {main:'#4f46e5', sun:'#f59e0b', moon:'#e5e7eb', fog:'#9ca3af', fogbank:'#d1d5db', light_cloud:'#f3f4f6', cloud:'#d1d5db', dark_cloud:'#6b7280', thunder:'#7c3aed', snow:'#f3f4f6', hail:'#60a5fa', sleet:'#60a5fa', wind:'#9ca3af', leaf:'#10b981', rain:'#3b82f6'}});
  skycons.add(canvas, Skycons[icon]); skycons.play();
});
