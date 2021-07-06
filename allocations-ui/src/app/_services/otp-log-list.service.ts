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
export class OtpLogListService {

  constructor(private http: HttpClient) { }

  getOtpLogList(postData: DefaultListApiParams ) {
    return this.http.post<any>(`${environment.apiUrl}/otpLogList/getOtpLogs`, postData);
  }

  exportOtpLogList(postData: DefaultListApiParams): Observable<any> {
    return this.http.post(`${environment.apiUrl}/otpLogList/getOtpLogs/export`, postData,
      {observe: 'response', responseType: 'blob'});
  }
}
