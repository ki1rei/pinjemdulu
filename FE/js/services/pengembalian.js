import { mockPengembalian } from '../data/pengembalian.js';
import { delay } from './client.js';

export async function getPengembalian() {
  await delay(50);
  return mockPengembalian;
}
