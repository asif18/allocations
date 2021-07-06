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
import { DefaultApiResponse } from '../_models';

@Injectable({
  providedIn: 'root'
})
export class IpBindingService {

  constructor(private http: HttpClient) { }

  saveIpBinding(postData: any): Observable<DefaultApiResponse> {
    return this.http.post<any>(`${environment.apiUrl}/ipBinding/saveIpBinding`, postData);
  }

  removeIpBinding(id: string) {
    return this.http.get<any>(`${environment.apiUrl}/ipBinding/removeIpBinding/${id}`);
  }

  updateStatus(id: string, action: string) {
    return this.http.get<any>(`${environment.apiUrl}/ipBinding/updateStatus/${id}/${action}`);
  }

  getIpBindings(): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/ipBinding/getIpBindings`);
  }
}