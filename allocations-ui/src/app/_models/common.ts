/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */

export interface DefaultApiResponse {
  status?: boolean;
  data?: any;
  message?: string | null;
}

export interface DefaultListApiParams {
  searchBy: string | object;
  startFrom: number;
  endTo: number;
  sortBy: string;
  sortDirection: string;
}

export interface UserInfo {
  id: string;
  name: string;
  email: string;
  username: string;
  role: string;
  type: string;
}
