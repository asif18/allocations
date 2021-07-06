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

import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { DefaultListApiParams } from '../_models';

@Injectable({
  providedIn: 'root'
})
export class InstanceService {

  constructor(private http: HttpClient) { }

  saveInstance(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/instance/saveInstance`, postData);
  }

  updateInstance(postData: any, encryptedId: string) {
    return this.http.post<any>(`${environment.apiUrl}/instance/updateInstance/${encryptedId}`, postData);
  }

  getInstance(encryptedId: string) {
    return this.http.get<any>(`${environment.apiUrl}/instance/getInstance/${encryptedId}`);
  }

  getInstances(postData: DefaultListApiParams ) {
    return this.http.post<any>(`${environment.apiUrl}/instance/getInstances`, postData);
  }

  exportInstances(postData: DefaultListApiParams): Observable<any> {
    return this.http.post(`${environment.apiUrl}/instance/getInstances/export`, postData,
      {observe: 'response', responseType: 'blob'});
  }
}
