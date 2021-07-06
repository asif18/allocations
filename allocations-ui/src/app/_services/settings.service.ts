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
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SettingsService {

  constructor(private http: HttpClient) { }

  getSettings(): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/settings/getSettings`);
  }

  saveSettings(postData?: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/settings/saveSettings`, postData);
  }

  getWifiUserSettings(): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/settings/getWifiUserSettings`);
  }

  saveWifiUserSettings(postData?: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/settings/saveWifiUserSettings`, postData);
  }

  uploadAdvertismentImage(image: File): Observable<any> {
    const formData = new FormData();
    formData.append('image', image);
    return this.http.post(`${environment.apiUrl}/settings/uploadAdvertismentImage`, formData);
  }

  removeAdvertismentImage(): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/settings/removeAdvertismentImage`);
  }

}
