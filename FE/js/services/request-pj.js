import { mockRequestPj } from '../data/request-pj.js';
import { delay } from './client.js';

export async function getRequestPj() {
  await delay(50);
  return mockRequestPj;
}
