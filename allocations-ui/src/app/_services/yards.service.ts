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
export class YardsService {

  constructor(private http: HttpClient) { }

  getUsageTimingLimits(postData: DefaultListApiParams ) {
    return this.http.post<any>(`${environment.apiUrl}/yards/getYards`, postData);
  }

  getYards(postData: DefaultListApiParams ) {
    return this.http.post<any>(`${environment.apiUrl}/yards/getYards`, postData);
  }

  removeYard(id: string) {
    return this.http.get<any>(`${environment.apiUrl}/yards/removeYard/${id}`);
  }

  saveYard(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/yards/saveYard`, postData);
  }
}
