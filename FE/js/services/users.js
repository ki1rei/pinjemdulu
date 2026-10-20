import { mockUsers } from '../data/users.js';
import { delay } from './client.js';

export async function getUsers() {
  await delay(50);
  return mockUsers;
}
