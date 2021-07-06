import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ActiveUsersService {

  constructor(private http: HttpClient) { }

  removeActiveUser(postData: any ) {
    return this.http.post<any>(`${environment.apiUrl}/activeUsers/removeActiveUser`, postData);
  }

  getActiveUsers() {
    return this.http.get<any>(`${environment.apiUrl}/activeUsers/getActiveUsers`);
  }
}
